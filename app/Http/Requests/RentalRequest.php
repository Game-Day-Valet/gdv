<?php


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RentalRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // 'user_id' => 'required|exists:users,id',
            'tournament_id' => 'required|exists:tournaments,id',
            'team_name' => 'required|string|max:255',
            'coach_name' => 'required|string|max:255',
            'field_number' => 'required|string|max:50',
            'items' => 'nullable|array',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'bundles' => 'nullable|array',
            'bundles.*' => 'exists:bundles,id',
            'instructions' => 'nullable|string',
            'drop_off_time' => 'nullable|date_format:Y-m-d H:i:s',
            'promo_code' => 'nullable|string|max:50',
            'insurance_option' => 'nullable|in:3,7,none',
            'damage_waiver' => 'nullable|boolean',
            'rental_date' => 'required|date',
            'delivery_assigned_to' => 'nullable|string',
            'payment_method' => 'nullable|in:stripe,apple_pay,google_pay',
            'payment_status' => 'nullable|in:pending,completed',
            'total_amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:pending,delivered,picked_up,returned',
            'return_instruction' => 'nullable|string',
        ];
    }
}
