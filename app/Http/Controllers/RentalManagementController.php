<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\RentalStatusLog;
use App\Models\User;
use App\Models\Tournament;
use App\Models\Item;
use App\Models\Bundle;
use App\Repositories\RentalRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $rentals = $this->rentalRepository->getAllPaginated(15);

        return view('rental_management.index', compact('rentals'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $rental = $this->rentalRepository->findWithRelations($id);

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
            'status' => 'required|in:pending,delivered,picked_up,returned',
            'notes' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // 2MB max
        ]);

        try {
            $this->rentalRepository->updateStatus(
                $id,
                $request->status,
                $request->notes,
                $request->user()->id,
                $request->file('image')
            );

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
}
