<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
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

                // Standardize damage_waiver to boolean
                if (array_key_exists('damage_waiver', $data)) {
                    $data['damage_waiver'] = (bool) $data['damage_waiver'];
                }

                // Ensure insurance_option is numeric or 'none'
                if (isset($data['insurance_option']) && $data['insurance_option'] === 'none') {
                    // keep as 'none' or set null per storage convention if needed
                }

                // Apply discount if eligible
                $data = $this->referralService->applyDiscount($user->id, $data);
                
                $rental = $this->rentalRepository->create($data);
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
