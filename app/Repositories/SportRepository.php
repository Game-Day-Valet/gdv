<?php


namespace App\Repositories;

use App\Models\Sport;
use App\Models\Tournament;
use Illuminate\Support\Facades\DB;
use App\Enums\SportStatus;
use App\Enums\TournamentStatus;

class SportRepository implements SportRepositoryInterface
{

    public function getAllActive()
    {
        return Sport::where('status', SportStatus::ACTIVE->value)->get();
    }

    public function find($id)
    {
        return Sport::findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            return Sport::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? SportStatus::ACTIVE->value,
            ]);
        });
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $sport = Sport::findOrFail($id);
            $sport->update([
                'name' => $data['name'] ?? $sport->name,
                'description' => $data['description'] ?? $sport->description,
                'status' => $data['status'] ?? $sport->status,
            ]);
            return $sport;
        });
    }

    public function delete($id)
    {
        $sport = Sport::findOrFail($id);
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
