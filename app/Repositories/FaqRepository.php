<?php


namespace App\Repositories;

use App\Models\Faq;
use Illuminate\Support\Facades\DB;

class FaqRepository implements FaqRepositoryInterface
{
    public function getAll()
    {
        return Faq::withTrashed()->get();
    }

    public function find($id)
    {
        return Faq::withTrashed()->findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            return Faq::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => $data['status'] ?? true,
            ]);
        });
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $faq = Faq::withTrashed()->findOrFail($id);
            $faq->update([
                'title' => $data['title'] ?? $faq->title,
                'description' => $data['description'] ?? $faq->description,
                'status' => $data['status'] ?? $faq->status,
            ]);
            return $faq;
        });
    }

    public function delete($id)
    {
        $faq = Faq::withTrashed()->findOrFail($id);
        $faq->delete();
    }
}
