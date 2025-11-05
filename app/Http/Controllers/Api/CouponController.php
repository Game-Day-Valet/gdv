<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use App\Repositories\CouponRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    protected $couponRepository;

    public function __construct(CouponRepositoryInterface $couponRepository)
    {
        $this->couponRepository = $couponRepository;
    }

    public function index()
    {
        $coupons = $this->couponRepository->getAllAvailable();
        return CouponResource::collection($coupons);
    }

    // public function validateCoupon(Request $request)
    // {
    //     // Validate required fields
    //     $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
    //         'promo_code' => 'required|string',
    //         'user_id' => 'required|integer|exists:users,id',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     // Check if coupon already used by this user
    //     $isUsed = \App\Models\Rental::where('user_id', $request->input('user_id'))
    //         ->where('promo_code', $request->input('promo_code'))
    //         ->where('payment_status', 'paid')
    //         ->exists();

    //     if ($isUsed) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Coupon validation failed',
    //             'errors' => [
    //                 'promo_code' => ['This coupon has already been used by this user.']
    //             ]
    //         ], 422);
    //     }

    //     // Find the coupon
    //     $coupon = Coupon::where('code', $request->input('promo_code'))->first();

    //     if (!$coupon) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Coupon validation failed',
    //             'errors' => [
    //                 'promo_code' => ['Invalid coupon code. Please check and try again.']
    //             ]
    //         ], 422);
    //     }

    //     // Check coupon validity period
    //     $now = Carbon::now();

    //     if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Coupon validation failed',
    //             'errors' => [
    //                 'promo_code' => ['This coupon is not yet active']
    //             ]
    //         ], 422);
    //     }

    //     if ($coupon->expires_at && $now->gt($coupon->expires_at)) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Coupon validation failed',
    //             'errors' => [
    //                 'promo_code' => ['This coupon has expired']
    //             ]
    //         ], 422);
    //     }

    //     // Check usage limits
    //     if ($coupon->max_uses && $coupon->used >= $coupon->max_uses) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Coupon validation failed',
    //             'errors' => [
    //                 'promo_code' => ['This coupon has reached its maximum usage limit.']
    //             ]
    //         ], 422);
    //     }

    //     // Coupon is valid
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Coupon is valid',
    //         'data' => [
    //             'coupon' => new CouponResource($coupon),
    //             'discount_type' => $coupon->type,
    //             'discount_value' => $coupon->value,
    //             'max_uses' => $coupon->max_uses,
    //             'used' => $coupon->used
    //         ]
    //     ], 200);
    // }



    public function validateCoupon(Request $request)
    {
        // Validate fields (user_id optional now)
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'promo_code' => 'required|string',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if coupon already used by this user (only if user_id provided)
        if ($request->filled('user_id')) {
            $isUsed = \App\Models\Rental::where('user_id', $request->input('user_id'))
                ->where('promo_code', $request->input('promo_code'))
                ->where('payment_status', 'paid')
                ->exists();

            if ($isUsed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon validation failed',
                    'errors' => [
                        'promo_code' => ['This coupon has already been used by this user.']
                    ]
                ], 422);
            }
        }

        // Find the coupon
        $coupon = \App\Models\Coupon::where('code', $request->input('promo_code'))->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon validation failed',
                'errors' => [
                    'promo_code' => ['Invalid coupon code. Please check and try again.']
                ]
            ], 422);
        }

        // Check coupon validity period
        $now = \Carbon\Carbon::now();

        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon validation failed',
                'errors' => [
                    'promo_code' => ['This coupon is not yet active.']
                ]
            ], 422);
        }

        if ($coupon->expires_at && $now->gt($coupon->expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon validation failed',
                'errors' => [
                    'promo_code' => ['This coupon has expired.']
                ]
            ], 422);
        }

        // Check usage limits
        if ($coupon->max_uses && $coupon->used >= $coupon->max_uses) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon validation failed',
                'errors' => [
                    'promo_code' => ['This coupon has reached its maximum usage limit.']
                ]
            ], 422);
        }

        // Coupon is valid
        return response()->json([
            'success' => true,
            'message' => 'Coupon is valid',
            'data' => [
                'coupon' => new \App\Http\Resources\CouponResource($coupon),
                'discount_type' => $coupon->type,
                'discount_value' => $coupon->value,
                'max_uses' => $coupon->max_uses,
                'used' => $coupon->used
            ]
        ], 200);
    }
}
