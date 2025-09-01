<?php


namespace App\Repositories;

use App\Models\Faq;
use Illuminate\Support\Facades\DB;

class FaqRepository implements FaqRepositoryInterface
{
    public function getAll()
    {
        return Faq::where('type', 'faq')->get();
    }

    public function find($id)
    {
        return Faq::where('type', 'faq')->findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            return Faq::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => $data['status'] ?? true,
                'type' => 'faq',
                'color' => $data['color'] ?? null,
            ]);
        });
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $faq = Faq::where('type', 'faq')->findOrFail($id);
            $faq->update([
                'title' => $data['title'] ?? $faq->title,
                'description' => $data['description'] ?? $faq->description,
                'status' => $data['status'] ?? $faq->status,
                'color' => array_key_exists('color', $data) ? $data['color'] : $faq->color,
            ]);
            return $faq;
        });
    }

    public function delete($id)
    {
        $faq = Faq::where('type', 'faq')->findOrFail($id);
        $faq->delete();
    }
}
