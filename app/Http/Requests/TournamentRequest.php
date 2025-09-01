<?php


namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\TournamentStatus;

class TournamentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        $rules = [
            'sport_id' => 'required|exists:sports,id',
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'required|string|max:255',
            'status' => 'nullable|in:' . implode(',', array_column(TournamentStatus::cases(), 'value')),
            // Tournament specific item/bundle assignments (keyed by id)
            'items' => 'nullable|array',
            'items.*.enabled' => 'nullable|boolean',
            'items.*.price' => 'nullable|numeric|min:0',
            'bundles' => 'nullable|array',
            'bundles.*.enabled' => 'nullable|boolean',
            'bundles.*.price' => 'nullable|numeric|min:0',
        ];

        // Image is required only on create; optional on update
        if ($this->isMethod('post')) {
            $rules['image'] = 'required|image|mimes:jpeg,png,jpg,gif|max:2048';
        } else {
            $rules['image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
        }

        return $rules;
    }
}
