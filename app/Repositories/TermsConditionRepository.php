<?php

namespace App\Repositories;

use App\Models\Faq;
use Illuminate\Support\Facades\DB;

class TermsConditionRepository implements TermsConditionRepositoryInterface
{
    public function getAll()
    {
        return Faq::where('type', 'terms_and_conditions')->get();
    }

    public function find($id)
    {
        return Faq::where('type', 'terms_and_conditions')->findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            return Faq::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => $data['status'] ?? true,
                'type' => 'terms_and_conditions',
            ]);
        });
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $terms = Faq::where('type', 'terms_and_conditions')->findOrFail($id);
            $terms->update([
                'title' => $data['title'] ?? $terms->title,
                'description' => $data['description'] ?? $terms->description,
                'status' => $data['status'] ?? $terms->status,
            ]);
            return $terms;
        });
    }

    public function delete($id)
    {
        $terms = Faq::where('type', 'terms_and_conditions')->findOrFail($id);
        $terms->delete();
    }
}


