<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\ReferralCode;
use App\Models\ReferralTracking;
use App\Models\UserCredit;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ReferralService
{
    public function generateCode($userId)
    {
        do {
            $code = 'GDV' . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (ReferralCode::where('code', $code)->exists());

        ReferralCode::create(['user_id' => $userId, 'code' => $code]);
        return $code;
    }

    public function trackReferral($referralCode, $newUserId)
    {
        $referral = ReferralCode::where('code', $referralCode)->first();
        if ($referral) {
            $referrerId = $referral->user_id;
            if (!ReferralTracking::where('referrer_id', $referrerId)->where('referred_user_id', $newUserId)->exists()) {
                $tracking = ReferralTracking::create([
                    'referrer_id' => $referrerId,
                    'referred_user_id' => $newUserId,
                    'referral_code' => $referralCode,
                ]);
                $this->awardCredit($referrerId);
                $tracking->update(['credit_awarded' => true]); // Mark credit awarded
                return $tracking;
            }
        }
        return null;
    }

    public function awardCredit($referrerId)
    {
        $credit = UserCredit::firstOrCreate(
            ['user_id' => $referrerId],
            ['amount' => 0.00, 'type' => 'referral']
        );
        $credit->increment('amount', 5.00);
        return $credit;
    }

    public function applyDiscount($userId, $rentalData)
    {
        $tracking = ReferralTracking::where('referred_user_id', $userId)->first();
        $rentalData = $this->applyCoupon($userId, $rentalData);

        if ($tracking && !$this->hasPreviousPaidRental($userId) && $rentalData['total_amount'] >= 10) {
            // Apply $5 discount (deduct from total_amount)
            $rentalData['total_amount'] = max(0, $rentalData['total_amount'] - 5.00);
            return $rentalData;
            // return true;
        }

        return $rentalData;
    }

    protected function applyCoupon($userId, $rentalData)
    {
        $isUsed = \App\Models\Rental::where('user_id', $userId)
            ->where('promo_code', $rentalData['promo_code'])
            ->where('payment_status', 'paid')
            ->exists();

        if ($isUsed) {
            return $rentalData;
        }

        $coupon = Coupon::where(function ($query) {
            $now = Carbon::now();
            $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
        })
            ->where(function ($query) {
                $now = Carbon::now();
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            })
            ->where(function ($query) {
                $query->whereNull('max_uses')->orWhereColumn('used', '<', 'max_uses');
            })
            ->where('code', $rentalData['promo_code'])->first();
        if ($coupon) {
            if ($coupon->type == 'percent') {
                $rentalData['total_amount'] = (float) ($rentalData['total_amount'] - ($rentalData['total_amount'] * ($coupon->value / 100)));
            } else {
                $rentalData['total_amount'] = (float) ($rentalData['total_amount'] - $coupon->value);
            }

            $coupon->increment('used');
        }

        return $rentalData;
    }
    protected function hasPreviousPaidRental($userId)
    {
        return \App\Models\Rental::where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->exists();
    }
}
