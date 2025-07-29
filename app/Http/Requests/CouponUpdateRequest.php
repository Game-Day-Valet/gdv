<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CouponUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $couponId = $this->route('coupon_management');
        return [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('coupons', 'code')->ignore($couponId)
            ],
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date|before_or_equal:expires_at',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'code.required' => 'The coupon code is required.',
            'code.unique' => 'This coupon code already exists.',
            'type.required' => 'The coupon type is required.',
            'type.in' => 'The coupon type must be either fixed or percent.',
            'value.required' => 'The coupon value is required.',
            'value.numeric' => 'The coupon value must be a number.',
            'value.min' => 'The coupon value must be at least 0.',
            'max_uses.integer' => 'The maximum uses must be a whole number.',
            'max_uses.min' => 'The maximum uses must be at least 1.',
            'starts_at.date' => 'The start date must be a valid date.',
            'starts_at.before_or_equal' => 'The start date must be before or equal to the expiry date.',
            'expires_at.date' => 'The expiry date must be a valid date.',
            'expires_at.after_or_equal' => 'The expiry date must be after or equal to the start date.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'code' => 'coupon code',
            'type' => 'coupon type',
            'value' => 'coupon value',
            'max_uses' => 'maximum uses',
            'starts_at' => 'start date',
            'expires_at' => 'expiry date',
        ];
    }
}
