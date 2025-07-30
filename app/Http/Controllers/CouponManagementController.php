<?php

namespace App\Http\Controllers;

use App\Http\Requests\CouponRequest;
use App\Http\Requests\CouponUpdateRequest;
use App\Repositories\CouponRepositoryInterface;
use Illuminate\Http\Request;

class CouponManagementController extends Controller
{
    protected $couponRepository;

    public function __construct(CouponRepositoryInterface $couponRepository)
    {
        $this->couponRepository = $couponRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $coupons = $this->couponRepository->getAll();
        return view('coupon_management.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('coupon_management.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CouponRequest $request)
    {
        try {
            $coupon = $this->couponRepository->create($request->validated(), []);

            return redirect()->route('coupon-management.index')
                ->with('success', value: 'Coupon created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create coupon. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $coupon = $this->couponRepository->find($id);
        return view('coupon_management.edit', compact('coupon'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CouponUpdateRequest $request, string $id)
    {
        try {
            $coupon = $this->couponRepository->update($id, $request->validated(), []);

            return redirect()->route('coupon-management.index')
                ->with('success', 'Coupon updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update coupon. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->couponRepository->delete($id);
            return redirect()->route('coupon-management.index')
                ->with('success', 'Coupon deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to delete coupon. Please try again.');
        }


    }
}
