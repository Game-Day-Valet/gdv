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
        return [
            'sport_id' => 'required|exists:sports,id',
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'required|string|max:255',
            'status' => 'nullable|in:' . implode(',', array_column(TournamentStatus::cases(), 'value')),
        ];
    }
}
