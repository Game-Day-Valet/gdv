<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\RentalStatusLog;
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

    public function __construct(RentalRepositoryInterface $rentalRepository)
    {
        $this->rentalRepository = $rentalRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        // Debug: Check raw database data first
        $rawRentals = DB::table('rentals')
            ->select('id', 'created_at', 'team_name')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            

        // If user is a manager, show only their assigned rentals
        if ($user->hasRole(Role::MANAGER)) {
            $rentals = $this->rentalRepository->getByManager($user->id, 15);
        } else {
            $rentals = $this->rentalRepository->getAllPaginated(15);
        }

        // Get all managers for the dropdown
        $managers = User::role(Role::MANAGER)->get();

        return view('rental_management.index', compact('rentals', 'managers'));
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
            $rental->items_with_data = $items;
        }

        // Load bundles data if bundles exist
        if ($rental->bundles) {
            $bundles = \App\Models\Bundle::whereIn('id', $rental->bundles)->get();
            $rental->bundles_with_data = $bundles;
        }

        return view('rental_management.show', compact('rental'));
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
            'estimated_delivery_time' => 'nullable|date',
            'assigned_manager_id' => 'nullable|exists:users,id',
        ]);

        try {
            // Get the current rental to capture old status
            $rental = $this->rentalRepository->find($id);
            $oldStatus = $rental->status;

            // Validate status progression
            if (!$this->isValidStatusProgression($oldStatus, $request->status)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status progression. You cannot go back to a previous status.'
                ], 400);
            }

            // Additional validation for confirmed status
            if ($request->status === 'confirmed') {
                if (empty($request->estimated_delivery_time)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Estimated delivery time is required when confirming a rental.'
                    ], 400);
                }
                if (empty($request->assigned_manager_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Manager assignment is required when confirming a rental.'
                    ], 400);
                }
            }

            // Check if user can update this rental
            $user = Auth::user();
            if ($user->hasRole(Role::MANAGER) && $rental->assigned_manager_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only update rentals assigned to you.'
                ], 403);
            }

            $this->rentalRepository->updateStatus(
                $id,
                $request->status,
                $request->notes,
                $user->id,
                $request->file('images'),
                $request->estimated_delivery_time,
                $request->assigned_manager_id
            );

            // Refresh the rental model to get updated data
            $rental->refresh();

            // Dispatch the event for real-time updates
            event(new RentalStatusUpdated($rental, $oldStatus, $request->status, $user->id));

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
            $this->rentalRepository->updatePaymentStatus($id, $request->payment_status);

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

            Log::info('Available statuses for rental ' . $id . ' with current status ' . $currentStatus . ': ' . json_encode($availableStatuses));

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
}
