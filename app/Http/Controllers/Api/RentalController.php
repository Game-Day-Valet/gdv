<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Events\RentalBookingCreated;
use App\Http\Requests\RentalRequest;
use App\Http\Resources\RentalResource;
use App\Http\Resources\RentalStatusLogResource;
use App\Repositories\RentalRepositoryInterface;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ReferralService;
use App\Models\Rental;
use App\Models\RentalStatusLog;
use Exception;
use Illuminate\Support\Facades\Log;

class RentalController extends Controller
{
    protected $rentalRepository;
    protected $referralService;

    public function __construct(RentalRepositoryInterface $rentalRepository, ReferralService $referralService)
    {
        $this->rentalRepository = $rentalRepository;
        $this->referralService = $referralService;
    }

    public function index()
    {
        $rentals = $this->rentalRepository->getAll();
        return RentalResource::collection($rentals);
    }

    public function userRentals()
    {
        $user = auth()->user();
        $rentals = Rental::where('user_id', $user->id)->with('tournament', 'statusLogs')->orderBy('created_at', 'desc')->get();
        return RentalResource::collection($rentals);
    }

    public function getRentalStatus($id)
    {
        $rentalStatus = RentalStatusLog::where('rental_id', $id)
            ->with(['updatedBy', 'rental'])
            ->get();
        return response()->json(['status' => RentalStatusLogResource::collection($rentalStatus)]);
    }

    public function store(RentalRequest $request)
    {
        try {
            $user = $request->user();
            if ($user && $user->hasRole(['user', 'super_admin'])) {
                $data = $request->validated();
                $data['user_id'] = $user->id;

                // Normalize items: support both [{item_id, quantity}] and {id: qty}
                $normalizedItems = [];
                if (!empty($data['items']) && is_array($data['items'])) {
                    foreach ($data['items'] as $key => $value) {
                        if (is_array($value) && isset($value['item_id'], $value['quantity'])) {
                            $qty = max(0, (int) $value['quantity']);
                            if ($qty > 0) {
                                $normalizedItems[] = [
                                    'item_id' => (string) $value['item_id'],
                                    'quantity' => $qty,
                                ];
                            }
                        } elseif (is_numeric($key)) {
                            // already normalized object array but missing fields -> skip
                            continue;
                        } else {
                            // legacy form: items: {"1": 2, "3": 1}
                            $qty = max(0, (int) $value);
                            if ($qty > 0) {
                                $normalizedItems[] = [
                                    'item_id' => (string) $key,
                                    'quantity' => $qty,
                                ];
                            }
                        }
                    }
                }
                $data['items'] = !empty($normalizedItems) ? $normalizedItems : null;

                // Normalize bundles: accept [10,4] or {"10":1, "4":1}
                $normalizedBundles = [];
                if (!empty($data['bundles']) && is_array($data['bundles'])) {
                    foreach ($data['bundles'] as $key => $value) {
                        if (is_numeric($value)) {
                            // array of ids
                            $normalizedBundles[] = (int) $value;
                        } elseif (is_numeric($key) && (int)$value > 0) {
                            // legacy object map where value is qty
                            $normalizedBundles[] = (int) $key;
                        }
                    }
                }
                $data['bundles'] = !empty($normalizedBundles) ? array_values(array_unique($normalizedBundles)) : null;

                // Standardize damage_waiver price
                $damageWaiverAmount = 0.0;
                if (isset($data['damage_waiver']) && is_numeric($data['damage_waiver'])) {
                    $damageWaiverAmount = (float) $data['damage_waiver'];
                } elseif (!empty($data['damage_waiver_options']) && is_array($data['damage_waiver_options'])) {
                    foreach ($data['damage_waiver_options'] as $v) { $damageWaiverAmount += (float) $v; }
                }
                $data['damage_waiver'] = $damageWaiverAmount > 0 ? $damageWaiverAmount : null;

                // Insurance price
                $insuranceAmount = 0.0;
                if (isset($data['insurance_option']) && is_numeric($data['insurance_option'])) {
                    $insuranceAmount = (float) $data['insurance_option'];
                }
                $data['insurance_option'] = $insuranceAmount > 0 ? $insuranceAmount : null;

                // Calculate total from items/bundles
                $calculatedTotal = 0;
                if (!empty($data['items']) && is_array($data['items'])) {
                    foreach ($data['items'] as $item) {
                        if (isset($item['item_id'], $item['quantity'])) {
                            $itemModel = \App\Models\Item::find($item['item_id']);
                            if ($itemModel) {
                                $calculatedTotal += (float) ($itemModel->price ?? 0) * (int) $item['quantity'];
                            }
                        }
                    }
                }
                if (!empty($data['bundles']) && is_array($data['bundles'])) {
                    foreach ($data['bundles'] as $bundleId) {
                        $bundleModel = \App\Models\Bundle::find($bundleId);
                        if ($bundleModel) {
                            $calculatedTotal += (float) ($bundleModel->price ?? 0);
                        }
                    }
                }
                
                // Add insurance and damage waiver
                $calculatedTotal += $insuranceAmount + $damageWaiverAmount;
                
                // Use frontend total if provided and reasonable, otherwise use calculated total
                $frontendTotal = (float) ($data['total_amount'] ?? 0);
                if ($frontendTotal > 0 && $frontendTotal <= $calculatedTotal) {
                    $data['total_amount'] = $frontendTotal;
                } else {
                    $data['total_amount'] = $calculatedTotal;
                }

                // Apply discount if eligible
                $data = $this->referralService->applyDiscount($user->id, $data);
                
                $rental = $this->rentalRepository->create($data);
                
                Log::info('Rental created successfully in API', [
                    'rental_id' => $rental->id,
                    'user_id' => $user->id,
                    'tournament_id' => $rental->tournament_id,
                    'total_amount' => $rental->total_amount,
                    'timestamp' => now()->toISOString()
                ]);
                
                // Dispatch event for booking confirmation email
                try {
                    // Check if we've already fired an event for this rental in this request
                    $eventKey = "event_fired_{$rental->id}";
                    if (cache()->has($eventKey)) {
                        Log::warning('Event already fired for this rental in this request, skipping duplicate', [
                            'rental_id' => $rental->id,
                            'user_id' => $user->id,
                            'controller' => 'Api\RentalController',
                            'method' => 'store',
                            'timestamp' => now()->toISOString()
                        ]);
                    } else {
                        // Mark that we've fired an event for this rental (cache for 1 minute)
                        cache()->put($eventKey, true, now()->addMinute());
                        
                        Log::info('Dispatching RentalBookingCreated event from API RentalController', [
                            'rental_id' => $rental->id,
                            'user_id' => $user->id,
                            'controller' => 'Api\RentalController',
                            'method' => 'store',
                            'timestamp' => now()->toISOString()
                        ]);
                        
                        event(new RentalBookingCreated($rental));
                        
                        Log::info('RentalBookingCreated event dispatched successfully from API RentalController', [
                            'rental_id' => $rental->id,
                            'timestamp' => now()->toISOString()
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to dispatch RentalBookingCreated event from API', [
                        'rental_id' => $rental->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'timestamp' => now()->toISOString()
                    ]);
                }
                
                return new RentalResource($rental);
            }
            throw new Exception('Unauthorized');
        } catch (Exception $e) {
            throw new Exception('Rental creation failed: ' . $e->getMessage(), 403);
        }
    }

    public function update(RentalRequest $request, $id)
    {
        try {
            $user = $request->user();
            if ($user && $user->hasRole(['user', 'super_admin'])) {
                $data = $request->validated();
                if (!isset($data['user_id'])) {
                    $data['user_id'] = $user->id;
                }
                $rental = $this->rentalRepository->update($id, $data);
                return new RentalResource($rental);
            }
            throw new Exception('Unauthorized');
        } catch (Exception $e) {
            throw new Exception('Rental update failed: ' . $e->getMessage(), 403);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            if ($user && $user->hasRole('super_admin')) {
                $this->rentalRepository->delete($id);
                return response()->json(['message' => 'Rental deleted successfully'], 200);
            }
            throw new Exception('Unauthorized');
        } catch (Exception $e) {
            throw new Exception('Rental deletion failed: ' . $e->getMessage(), 403);
        }
    }
}
