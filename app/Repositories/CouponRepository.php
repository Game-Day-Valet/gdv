<?php

namespace App\Repositories;

use App\Models\Coupon;
use Illuminate\Support\Carbon;

class CouponRepository implements CouponRepositoryInterface
{
    public function getAllAvailable()
    {
        return Coupon::where(function ($query) {
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
            ->get();
    }

    public function getAll()
    {
        return Coupon::all();
    }

    public function find($id)
    {
        return Coupon::findOrFail($id);
    }

    public function create(array $data, array $items)
    {
        // $items is unused in this basic case, but can be used for product/category linkage
        return Coupon::create($data);
    }

    public function update($id, array $data, array $items)
    {
        $coupon = $this->find($id);
        $coupon->update($data);
        return $coupon;
    }

    public function delete($id)
    {
        $coupon = $this->find($id);
        return $coupon->delete();
    }
}
