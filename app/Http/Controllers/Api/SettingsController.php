<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookingOption;

class SettingsController extends Controller
{
    public function booking()
    {
        $insurance = BookingOption::where('type', 'insurance')->where('enabled', true)->orderByRaw('COALESCE(sort_order, 999999) asc')->get(['id','label','description','price']);
        $waivers = BookingOption::where('type', 'damage_waiver')->where('enabled', true)->orderByRaw('COALESCE(sort_order, 999999) asc')->get(['id','label','description','price']);
        return response()->json([
            'insurance_options' => $insurance->map(fn($o)=>[
                'id' => (int) $o->id,
                'label' => $o->label,
                'description' => $o->description,
                'price' => (float) $o->price,
            ]),
            'damage_waiver_options' => $waivers->map(fn($o)=>[
                'id' => (int) $o->id,
                'label' => $o->label,
                'description' => $o->description,
                'price' => (float) $o->price,
            ]),
        ]);
    }
} 