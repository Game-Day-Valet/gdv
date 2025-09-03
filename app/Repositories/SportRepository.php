<?php


namespace App\Repositories;

use App\Models\Sport;
use App\Models\Tournament;
use Illuminate\Support\Facades\DB;
use App\Enums\SportStatus;
use App\Enums\TournamentStatus;
use Illuminate\Support\Facades\Storage;

class SportRepository implements SportRepositoryInterface
{

    public function getAllActive()
    {
        return Sport::where('status', SportStatus::ACTIVE->value)
            ->orderByRaw('COALESCE(sort_order, 999999) asc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function find($id)
    {
        return Sport::findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $imagePath = null;
            if (isset($data['image']) && $data['image']) {
                $imagePath = $data['image']->store('sports', 'public');
            }
            return Sport::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'image' => $imagePath,
                'status' => $data['status'] ?? SportStatus::ACTIVE->value,
            ]);
        });
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $sport = Sport::findOrFail($id);
            $imagePath = $sport->image;
            if (isset($data['image']) && $data['image']) {
                if ($imagePath) { Storage::disk('public')->delete($imagePath); }
                $imagePath = $data['image']->store('sports', 'public');
            }
            $sport->update([
                'name' => $data['name'] ?? $sport->name,
                'description' => $data['description'] ?? $sport->description,
                'image' => $imagePath,
                'status' => $data['status'] ?? $sport->status,
            ]);
            return $sport;
        });
    }

    public function delete($id)
    {
        $sport = Sport::findOrFail($id);
        if ($sport->image) { Storage::disk('public')->delete($sport->image); }
        $sport->delete();
    }

    public function getTournamentsBySport($sportId, $pagination = false, $limit = 10)
    {
        $tournaments = Tournament::where('sport_id', $sportId)
            ->where('status', TournamentStatus::ACTIVE->value)
            ->with('sport');
        if ($pagination) {
            return $tournaments->paginate($limit);
        }
        return $tournaments->get();
    }
}
