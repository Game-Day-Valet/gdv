<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\TournamentStatus;

class TournamentUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'sport_id' => 'sometimes|required|exists:sports,id',
            'name' => 'sometimes|required|string|max:255',
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'sometimes|required|string|max:255',
            'status' => 'nullable|in:' . implode(',', array_column(TournamentStatus::cases(), 'value')),
        ];
    }

    public function messages()
    {
        return [
            'sport_id.required' => 'Sport is required.',
            'sport_id.exists' => 'Selected sport does not exist.',
            'name.required' => 'Tournament name is required.',
            'name.max' => 'Tournament name cannot exceed 255 characters.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif.',
            'image.max' => 'The image may not be greater than 2MB.',
            'start_date.required' => 'Start date is required.',
            'start_date.date' => 'Start date must be a valid date.',
            'end_date.date' => 'End date must be a valid date.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'location.required' => 'Location is required.',
            'location.max' => 'Location cannot exceed 255 characters.',
            'status.in' => 'Invalid status value.',
        ];
    }
}
