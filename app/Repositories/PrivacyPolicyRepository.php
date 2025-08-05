<?php

namespace App\Repositories;

use App\Models\Faq;
use Illuminate\Support\Facades\DB;

class PrivacyPolicyRepository implements PrivacyPolicyRepositoryInterface
{
    public function getAll()
    {
        return Faq::where('type', 'privacy_policy')->get();
    }

    public function find($id)
    {
        return Faq::where('type', 'privacy_policy')->findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            return Faq::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => $data['status'] ?? true,
                'type' => 'privacy_policy',
            ]);
        });
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $privacyPolicy = Faq::where('type', 'privacy_policy')->findOrFail($id);
            $privacyPolicy->update([
                'title' => $data['title'] ?? $privacyPolicy->title,
                'description' => $data['description'] ?? $privacyPolicy->description,
                'status' => $data['status'] ?? $privacyPolicy->status,
            ]);
            return $privacyPolicy;
        });
    }

    public function delete($id)
    {
        $privacyPolicy = Faq::where('type', 'privacy_policy')->findOrFail($id);
        $privacyPolicy->delete();
    }
}
