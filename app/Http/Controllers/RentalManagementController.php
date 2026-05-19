<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\RentalStatusLog;
use App\Services\AirtableService;
use App\Models\User;
use App\Models\Tournament;
use App\Models\Item;
use App\Models\Bundle;
use App\Repositories\RentalRepositoryInterface;
use App\Events\RentalStatusUpdated;
use App\Enums\RentalStatus;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RentalManagementController extends Controller
{
    protected $rentalRepository;
    protected $airtable;

    public function __construct(RentalRepositoryInterface $rentalRepository, AirtableService $airtable)
    {
        $this->rentalRepository = $rentalRepository;
        $this->airtable = $airtable;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $filters = $request->only(['sport_id', 'tournament_id', 'location', 'status', 'payment_status', 'coach_name', 'team_name']);

        // Default index shows only PAID/COMPLETED rentals
        if ($user->hasRole(Role::MANAGER)) {
            $rentals = $this->rentalRepository->getByManagerPaid($user->id, 15, $filters);
        } else {
            $rentals = $this->rentalRepository->getPaid($filters);
        }

        // Get all managers for the dropdown
        $managers = User::role(Role::MANAGER)->get();
        $sports = \App\Models\Sport::all();
        $tournaments = \App\Models\Tournament::withTrashed()->withoutGlobalScopes()->orderBy('name')->get();
        $locations = \App\Models\Tournament::withoutGlobalScopes()->whereNotNull('location')->distinct()->pluck('location');

        return view('rental_management.index', compact('rentals', 'managers', 'sports', 'tournaments', 'locations'));
    }

    /**
     * Display pending payment rentals.
     */
    public function pending(Request $request)
    {
        $user = Auth::user();
        $filters = $request->only(['sport_id', 'tournament_id', 'location', 'status', 'payment_status', 'coach_name', 'team_name']);

        if ($user->hasRole(Role::MANAGER)) {
            $rentals = $this->rentalRepository->getByManagerPending($user->id, 15, $filters);
        } else {
            $rentals = $this->rentalRepository->getPending($filters);
        }

        $managers = User::role(Role::MANAGER)->get();
        $sports = \App\Models\Sport::all();
        $tournaments = \App\Models\Tournament::withTrashed()->withoutGlobalScopes()->orderBy('name')->get();
        $locations = \App\Models\Tournament::withoutGlobalScopes()->whereNotNull('location')->distinct()->pluck('location');

        return view('rental_management.index', compact('rentals', 'managers', 'sports', 'tournaments', 'locations'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $rental = $this->rentalRepository->findWithRelations($id);

        // Check if current user can view this rental
        $user = Auth::user();
        if ($user->hasRole(Role::MANAGER) && $rental->assigned_manager_id !== $user->id) {
            abort(403, 'You can only view rentals assigned to you.');
        }

        // Load items data if items exist
        if ($rental->items) {
            $itemIds = collect($rental->items)->pluck('item_id')->toArray();
            $items = \App\Models\Item::whereIn('id', $itemIds)->get()->keyBy('id');

            // Check for tournament specific pricing
            if ($rental->tournament_id) {
                $tournamentItems = DB::table('tournament_item')
                    ->where('tournament_id', $rental->tournament_id)
                    ->whereIn('item_id', $itemIds)
                    ->get()
                    ->keyBy('item_id');

                foreach ($items as $itemId => $item) {
                    if (isset($tournamentItems[$itemId]) && !is_null($tournamentItems[$itemId]->price)) {
                        $item->price = $tournamentItems[$itemId]->price;
                    }
                }
            }

            $rental->items_with_data = $items;
        }

        // Load bundles data if bundles exist (supports new structure with quantities)
        if ($rental->bundles) {
            $bundleIds = [];
            $bundleQtyMap = [];
            if (is_array($rental->bundles)) {
                foreach ($rental->bundles as $b) {
                    if (is_array($b) && isset($b['bundle_id'])) {
                        $bid = (int) $b['bundle_id'];
                        $qty = isset($b['quantity']) ? (int) $b['quantity'] : 1;
                        $bundleIds[] = $bid;
                        $bundleQtyMap[$bid] = ($bundleQtyMap[$bid] ?? 0) + max(1, $qty);
                    } elseif (is_numeric($b)) {
                        $bid = (int) $b;
                        $bundleIds[] = $bid;
                        $bundleQtyMap[$bid] = ($bundleQtyMap[$bid] ?? 0) + 1;
                    }
                }
            }
            $bundleIds = array_values(array_unique($bundleIds));
            $bundles = \App\Models\Bundle::whereIn('id', $bundleIds)->get()->keyBy('id');

            // Check for tournament specific pricing for bundles
            if ($rental->tournament_id) {
                $tournamentBundles = DB::table('tournament_bundle')
                    ->where('tournament_id', $rental->tournament_id)
                    ->whereIn('bundle_id', $bundleIds)
                    ->get()
                    ->keyBy('bundle_id');

                foreach ($bundles as $bundleId => $bundle) {
                    if (isset($tournamentBundles[$bundleId]) && !is_null($tournamentBundles[$bundleId]->price)) {
                        $bundle->price = $tournamentBundles[$bundleId]->price;
                    }
                }
            }

            $rental->bundles_with_data = $bundles;
            $rental->bundle_quantities = $bundleQtyMap;
        }



        // Fetch coupon details if promo code exists
        $coupon = null;
        if ($rental->promo_code) {
            $coupon = \App\Models\Coupon::where('code', $rental->promo_code)->first();
        }

        return view('rental_management.show', compact('rental', 'coupon'));
    }

    /**
     * Update rental status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,out_for_delivery,delivered,cancelled',
            'notes' => 'nullable|string|max:500',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max per image
            // 'estimated_delivery_time' => 'nullable|date', // disabled for now
            'assigned_manager_id' => 'nullable|exists:users,id',
        ]);

        try {
            // Get the current rental to capture old status
            $rental = $this->rentalRepository->find($id);
            $oldStatus = $rental->status;

            // Block updates if payment is pending
            if (strtolower((string) ($rental->payment_status ?? 'pending')) === 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Not allowed until payment completed'
                ], 400);
            }

            // Validate status progression
            if (!$this->isValidStatusProgression($oldStatus, $request->status)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status progression. You cannot go back to a previous status.'
                ], 400);
            }

            // Additional validation for confirmed status (estimated time disabled)
            if ($request->status === 'confirmed') {
                if (empty($request->assigned_manager_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Manager assignment is required when confirming a rental.'
                    ], 400);
                }
            }

            $this->rentalRepository->updateStatus(
                $id,
                $request->status,
                $request->notes,
                $user->id ?? Auth::id(),
                $request->file('images'),
                null, // estimated delivery time disabled
                $request->assigned_manager_id
            );


            // Refresh the rental model to get updated data
            $rental->refresh();

            try {
                $this->airtable->updateOrInsertRental($rental);
            } catch (\Throwable $e) {
                Log::error('Airtable sync failed on updateStatus', ['error' => $e->getMessage()]);
            }

            // Dispatch the event for real-time updates
            event(new RentalStatusUpdated($rental, $oldStatus, $request->status, Auth::id()));

            return response()->json([
                'success' => true,
                'message' => 'Rental status updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,completed'
        ]);

        try {
            $rental = $this->rentalRepository->updatePaymentStatus($id, $request->payment_status);

            try {
                $this->airtable->updateOrInsertRental($rental);
            } catch (\Throwable $e) {
                Log::error('Airtable sync failed on updatePaymentStatus', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign or change manager for a rental without altering status
     */
    public function assignManager(Request $request, $id)
    {
        $request->validate([
            'assigned_manager_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        if (!$user || !$user->hasRole(Role::SUPER_ADMIN)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            $rental = Rental::findOrFail($id);
            $rental->assigned_manager_id = (int) $request->input('assigned_manager_id');
            $rental->save();

            // Optionally log in status logs
            try {
                RentalStatusLog::create([
                    'rental_id' => $rental->id,
                    'status' => $rental->status,
                    'notes' => 'Manager reassigned by admin',
                    'image_paths' => null,
                    'updated_by' => $user->id,
                ]);
            } catch (\Throwable $e) { /* ignore */
            }

            return response()->json(['success' => true, 'message' => 'Assigned manager updated successfully.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Failed to assign manager: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get available statuses based on current status
     */
    public function getAvailableStatuses($id)
    {
        try {
            $rental = $this->rentalRepository->find($id);

            if (!$rental) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rental not found'
                ], 404);
            }

            $currentStatus = $rental->status;
            $availableStatuses = $this->getNextAvailableStatuses($currentStatus);


            return response()->json([
                'success' => true,
                'available_statuses' => $availableStatuses,
                'current_status' => $currentStatus,
                'debug' => [
                    'rental_id' => $id,
                    'current_status' => $currentStatus,
                    'available_statuses' => $availableStatuses
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting available statuses: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting available statuses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate status progression
     */
    private function isValidStatusProgression($currentStatus, $newStatus)
    {
        $validProgressions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['out_for_delivery', 'cancelled'],
            'out_for_delivery' => ['delivered', 'cancelled'],
            'delivered' => [], // Final status
            'cancelled' => [], // Final status
        ];

        return in_array($newStatus, $validProgressions[$currentStatus] ?? []);
    }

    /**
     * Get next available statuses
     */
    private function getNextAvailableStatuses($currentStatus)
    {
        $validProgressions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['out_for_delivery', 'cancelled'],
            'out_for_delivery' => ['delivered', 'cancelled'],
            'delivered' => [],
            'cancelled' => [],
        ];

        return $validProgressions[$currentStatus] ?? [];
    }

    /**
     * Remove the specified rental (admin only)
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            if (!$user || !$user->hasRole(Role::SUPER_ADMIN)) {
                return redirect()->back()->with('error', 'Unauthorized');
            }

            $this->rentalRepository->delete($id);

            return redirect()->back()->with('success', 'Rental deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete rental: ' . $e->getMessage());
        }
    }
}
