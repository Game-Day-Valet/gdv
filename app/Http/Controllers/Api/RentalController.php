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
use App\Services\AirtableService;
use App\Models\Rental;
use App\Models\RentalStatusLog;
use App\Notifications\RentalBookingConfirmationNotification;
use Exception;
use Illuminate\Support\Facades\Log;

class RentalController extends Controller
{
    protected $rentalRepository;
    protected $referralService;
    protected $airtableService;

    public function __construct(RentalRepositoryInterface $rentalRepository, ReferralService $referralService, AirtableService $airtableService)
    {
        $this->rentalRepository = $rentalRepository;
        $this->referralService = $referralService;
        $this->airtableService = $airtableService;
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

                // Normalize bundles: accept [{bundle_id, quantity}], [id], or {id: qty}
                $normalizedBundles = [];
                if (!empty($data['bundles']) && is_array($data['bundles'])) {
                    foreach ($data['bundles'] as $key => $value) {
                        if (is_array($value) && isset($value['bundle_id'])) {
                            $qty = max(0, (int) ($value['quantity'] ?? 1));
                            if ($qty > 0) {
                                $normalizedBundles[] = [
                                    'bundle_id' => (string) $value['bundle_id'],
                                    'quantity' => $qty,
                                ];
                            }
                        } elseif (!is_array($value) && is_numeric($key)) {
                            // legacy: {"10": 2}
                            $qty = max(0, (int) $value);
                            if ($qty > 0) {
                                $normalizedBundles[] = [
                                    'bundle_id' => (string) $key,
                                    'quantity' => $qty,
                                ];
                            }
                        } elseif (is_numeric($value)) {
                            // legacy array of ids [10,4]
                            $normalizedBundles[] = [
                                'bundle_id' => (string) $value,
                                'quantity' => 1,
                            ];
                        }
                    }
                }
                $data['bundles'] = !empty($normalizedBundles) ? $normalizedBundles : null;

                // Standardize damage_waiver price
                $damageWaiverAmount = 0.0;
                if (isset($data['damage_waiver']) && is_numeric($data['damage_waiver'])) {
                    $damageWaiverAmount = (float) $data['damage_waiver'];
                } elseif (!empty($data['damage_waiver_options']) && is_array($data['damage_waiver_options'])) {
                    foreach ($data['damage_waiver_options'] as $v) {
                        $damageWaiverAmount += (float) $v;
                    }
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
                    foreach ($data['bundles'] as $bundle) {
                        if (isset($bundle['bundle_id'])) {
                            $bundleModel = \App\Models\Bundle::find($bundle['bundle_id']);
                            if ($bundleModel) {
                                $calculatedTotal += (float) ($bundleModel->price ?? 0) * (int) ($bundle['quantity'] ?? 1);
                            }
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

                // booking_days: allow 1-7 days if provided (nullable)
                if (isset($data['booking_days'])) {
                    $bd = (int) $data['booking_days'];
                    if ($bd < 1 || $bd > 7) {
                        $data['booking_days'] = null;
                    } else {
                        $data['booking_days'] = $bd;
                    }
                }

                // For mobile/API: do NOT send confirmation on create. Payment confirmation will be handled on update when payment_status becomes completed
                $rental = $this->rentalRepository->create($data);

                // Sync to Airtable
                try {
                    $this->airtableService->updateOrInsertRental($rental);
                } catch (\Exception $e) {
                    Log::error('Airtable sync failed in API store: ' . $e->getMessage());
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
                // Capture pre-update payment status
                $existing = Rental::findOrFail($id);
                $beforePayment = (string) ($existing->payment_status ?? 'pending');

                $rental = $this->rentalRepository->update($id, $data);

                // If payment_status moved to completed, send confirmation now
                $afterPayment = (string) ($rental->payment_status ?? 'pending');
                if ($beforePayment !== 'completed' && $afterPayment === 'completed') {
                    try {
                        $eventKey = "rental_payment_completed_{$rental->id}";
                        if (!cache()->has($eventKey)) {
                            cache()->put($eventKey, true, now()->addMinutes(5));
                            // Send in-app notification for mobile
                            $user->notify(new RentalBookingConfirmationNotification($rental, $user));
                            // Dispatch email/SMS job via existing listener/job using the same template
                            event(new RentalBookingCreated($rental));
                        }
                    } catch (\Throwable $e) {
                        Log::error('Failed to queue booking confirmation after payment', ['rental_id' => $rental->id, 'error' => $e->getMessage()]);
                    }
                }

                // Sync to Airtable
                try {
                    $this->airtableService->updateOrInsertRental($rental);
                } catch (\Exception $e) {
                    Log::error('Airtable sync failed in API update: ' . $e->getMessage());
                }

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
