<?php

namespace App\Repositories;

use App\Models\Tournament;
use Illuminate\Support\Facades\DB;
use App\Enums\TournamentStatus;
use Illuminate\Support\Facades\Storage;

class TournamentRepository implements TournamentRepositoryInterface
{
    // public function getAllActive()
    // {
    //     return Tournament::where('status', TournamentStatus::ACTIVE->value)
    //         ->with('sport')
    //         ->get();
    // }

    // public function getAllActive($search = null, $pagination = false, $limit = 10)
    // {
    //     $query = Tournament::where('status', TournamentStatus::ACTIVE->value)
    //         ->with('sport');

    //     if ($search) {
    //         $searchTerms = preg_split('/\s+/', trim($search));

    //         $query->where(function ($q) use ($searchTerms) {
    //             foreach ($searchTerms as $term) {
    //                 // Check if term looks like a date (YYYY-MM-DD format)
    //                 if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $term)) {
    //                     $q->where(function ($dateQuery) use ($term) {
    //                         $dateQuery->whereDate('start_date', $term)
    //                             ->orWhereDate('end_date', $term)
    //                             ->orWhere(function ($between) use ($term) {
    //                                 $between->whereDate('start_date', '<=', $term)
    //                                     ->whereDate('end_date', '>=', $term);
    //                             });
    //                     });
    //                 }
    //             }
    //         });
    //     }


    //     if ($pagination === true) {
    //         return $query->paginate($limit);
    //     }
    //     return $query->get();
    // }

    public function getAllActive($search = null, $pagination = false, $limit = 10)
    {
        $query = Tournament::where('status', TournamentStatus::ACTIVE->value)
            ->with('sport');

        if ($search) {
            $searchTerms = preg_split('/\s+/', trim($search));

            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    // Check if term looks like a date (YYYY-MM-DD format)
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $term)) {
                        $q->orWhere(function ($dateQuery) use ($term) {
                            $dateQuery->whereDate('start_date', $term)
                                ->orWhereDate('end_date', $term)
                                ->orWhere(function ($between) use ($term) {
                                    $between->whereDate('start_date', '<=', $term)
                                        ->whereDate('end_date', '>=', $term);
                                });
                        });
                    } else {
                        // Search by name or location (partial match)
                        $q->orWhere('name', 'LIKE', "%{$term}%")
                            ->orWhere('location', 'LIKE', "%{$term}%");
                    }
                }
            });
        }

        if ($pagination === true) {
            return $query->paginate($limit);
        }
        return $query->get();
    }


    public function updateSortOrders(array $orders)
    {
        // $orders is array of ['id' => int, 'sort_order' => int]
        DB::transaction(function () use ($orders) {
            foreach ($orders as $o) {
                if (!isset($o['id']) || !isset($o['sort_order']))
                    continue;
                Tournament::where('id', $o['id'])->update(['sort_order' => (int) $o['sort_order']]);
            }
        });
    }

    public function getTodaysTournaments()
    {
        $today = now()->toDateString();
        return Tournament::where('status', TournamentStatus::ACTIVE->value)
            ->where(function ($query) use ($today) {
                $query->where(function ($q) use ($today) {
                    $q->whereDate('start_date', '<=', $today)
                        ->whereDate('end_date', '>=', $today);
                })
                    ->orWhere(function ($q) use ($today) {
                        $q->whereDate('start_date', $today)
                            ->whereNull('end_date');
                    });
            })
            ->with('sport')
            ->get();
    }

    public function find($id)
    {
        return Tournament::with(['sport', 'items', 'bundles'])->findOrFail($id);
    }

    public function findBySlug($slug)
    {
        return Tournament::withTrashed()->with(['sport', 'items', 'bundles'])
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug)
                    ->orWhere('id', $slug);
            })
            ->firstOrFail();
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $imagePath = null;
            if (isset($data['image']) && $data['image']) {
                // Store image in storage/app/public/tournaments
                $imagePath = $data['image']->store('tournaments', 'public');
            }

            $tournament = Tournament::create([
                'sport_id' => $data['sport_id'],
                'name' => $data['name'],
                'image' => $imagePath, // Store relative path
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'game_date' => $data['game_date'] ?? null,
                'game_time' => $data['game_time'] ?? null,
                'location' => $data['location'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? TournamentStatus::ACTIVE->value,
                'tax_rate' => $data['tax_rate'] ?? 0,
            ]);

            // Attach items if provided: items[itemId] => ['enabled' => bool, 'price' => number|null]
            if (!empty($data['items']) && is_array($data['items'])) {
                $syncItems = [];
                foreach ($data['items'] as $itemId => $payload) {
                    if (empty($payload['enabled']))
                        continue; // only when checkbox is checked
                    $pivot = [];
                    if (isset($payload['price']) && $payload['price'] !== '' && $payload['price'] !== null) {
                        $pivot['price'] = (float) $payload['price'];
                    }
                    $syncItems[(int) $itemId] = $pivot;
                }
                if (!empty($syncItems)) {
                    $tournament->items()->sync($syncItems);
                }
            }

            // Attach bundles if provided: bundles[bundleId] => ['enabled' => bool, 'price' => number|null]
            if (!empty($data['bundles']) && is_array($data['bundles'])) {
                $syncBundles = [];
                foreach ($data['bundles'] as $bundleId => $payload) {
                    if (empty($payload['enabled']))
                        continue; // only when checkbox is checked
                    $pivot = [];
                    if (isset($payload['price']) && $payload['price'] !== '' && $payload['price'] !== null) {
                        $pivot['price'] = (float) $payload['price'];
                    }
                    $syncBundles[(int) $bundleId] = $pivot;
                }
                if (!empty($syncBundles)) {
                    $tournament->bundles()->sync($syncBundles);
                }
            }

            return $tournament;

            // Optional Airtable sync (commented out)
            /*
            if (config('services.airtable.enabled')) {
                Http::withToken(config('services.airtable.api_key'))
                    ->post('https://api.airtable.com/v0/' . config('services.airtable.base_id') . '/Tournaments', [
                        'fields' => [
                            'Name' => $tournament->name,
                            'Start Date' => $tournament->start_date,
                            'End Date' => $tournament->end_date,
                            'Location' => $tournament->location,
                            'Status' => $tournament->status,
                            'Dates' => json_encode($tournament->dates),
                        ],
                    ]);
            }
            */
        });
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $tournament = Tournament::findOrFail($id);

            $imagePath = $tournament->image;
            if (isset($data['image']) && $data['image']) {
                // Delete old image if it exists
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
                // Store new image
                $imagePath = $data['image']->store('tournaments', 'public');
            } elseif (array_key_exists('image', $data) && is_null($data['image'])) {
                // If image is explicitly set to null, delete the existing image
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = null;
            }

            $tournament->update([
                'sport_id' => $data['sport_id'] ?? $tournament->sport_id,
                'name' => $data['name'] ?? $tournament->name,
                'image' => $imagePath,
                'start_date' => $data['start_date'] ?? $tournament->start_date,
                'end_date' => array_key_exists('end_date', $data) ? $data['end_date'] : $tournament->end_date,
                'game_date' => array_key_exists('game_date', $data) ? $data['game_date'] : $tournament->game_date,
                'game_time' => array_key_exists('game_time', $data) ? $data['game_time'] : $tournament->game_time,
                'location' => $data['location'] ?? $tournament->location,
                'description' => array_key_exists('description', $data) ? $data['description'] : $tournament->description,
                'status' => $data['status'] ?? $tournament->status,
                'tax_rate' => array_key_exists('tax_rate', $data) ? $data['tax_rate'] : $tournament->tax_rate,
            ]);

            // Sync items if provided
            if (array_key_exists('items', $data)) {
                $syncItems = [];
                foreach ((array) $data['items'] as $itemId => $payload) {
                    if (empty($payload['enabled']))
                        continue; // only attach checked
                    $pivot = [];
                    if (isset($payload['price']) && $payload['price'] !== '' && $payload['price'] !== null) {
                        $pivot['price'] = (float) $payload['price'];
                    }
                    $syncItems[(int) $itemId] = $pivot;
                }
                $tournament->items()->sync($syncItems);
            }

            // Sync bundles if provided
            if (array_key_exists('bundles', $data)) {
                $syncBundles = [];
                foreach ((array) $data['bundles'] as $bundleId => $payload) {
                    if (empty($payload['enabled']))
                        continue; // only attach checked
                    $pivot = [];
                    if (isset($payload['price']) && $payload['price'] !== '' && $payload['price'] !== null) {
                        $pivot['price'] = (float) $payload['price'];
                    }
                    $syncBundles[(int) $bundleId] = $pivot;
                }
                $tournament->bundles()->sync($syncBundles);
            }

            // Optional Airtable sync (commented out)
            /*
            if (config('services.airtable.enabled')) {
                Http::withToken(config('services.airtable.api_key'))
                    ->patch('https://api.airtable.com/v0/' . config('services.airtable.base_id') . '/Tournaments/' . $tournament->airtable_id, [
                        'fields' => [
                            'Name' => $tournament->name,
                            'Start Date' => $tournament->start_date,
                            'End Date' => $tournament->end_date,
                            'Location' => $tournament->location,
                            'Status' => $tournament->status,
                            'Dates' => json_encode($tournament->dates),
                        ],
                    ]);
            }
            */

            return $tournament;
        });
    }


    public function delete($id)
    {
        $tournament = Tournament::findOrFail($id);
        // Delete image from storage if it exists
        if ($tournament->image) {
            Storage::disk('public')->delete($tournament->image);
        }
        $tournament->delete();

        // Optional Airtable sync (commented out)
        /*
        if (config('services.airtable.enabled')) {
            Http::withToken(config('services.airtable.api_key'))
                ->delete('https://api.airtable.com/v0/' . config('services.airtable.base_id') . '/Tournaments/' . $tournament->airtable_id);
        }
        */
    }
}
