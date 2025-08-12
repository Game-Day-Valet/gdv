<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer'],
            'is_bundle' => ['required', 'boolean'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}


