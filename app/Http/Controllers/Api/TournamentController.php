<?php


namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Http\Requests\Api\TournamentRequest;
use App\Http\Requests\Api\TournamentUpdateRequest;
use App\Http\Resources\TournamentResource;
use App\Repositories\TournamentRepositoryInterface;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tournament;
use Exception;
use App\Enums\TournamentStatus;

class TournamentController extends Controller
{
    protected $tournamentRepository;

    public function __construct(TournamentRepositoryInterface $tournamentRepository)
    {
        $this->tournamentRepository = $tournamentRepository;
    }

    public function index(Request $request)
    {
        $search = $request->query('search');

        $tournaments = $this->tournamentRepository->getAllActive($search, true, $request->limit);
        return TournamentResource::collection($tournaments);
    }

    public function store(TournamentRequest $request)
    {
        try {
            $user = $request->user();
            if ($user && ($user->hasPermissionTo(Permission::SUPER_ADMIN->value) ||
                $user->hasPermissionTo(Permission::MANAGER->value))) {
                $tournament = $this->tournamentRepository->create($request->validated());
                return new TournamentResource($tournament);
            }
            throw new Exception('Unauthorized');
        } catch (Exception $e) {
            throw new Exception('Tournament creation failed: ' . $e->getMessage(), 403);
        }
    }

    public function update(TournamentUpdateRequest $request, $id)
    {
        try {
            $user = $request->user();
            if ($user && ($user->hasPermissionTo(Permission::SUPER_ADMIN->value) ||
                $user->hasPermissionTo(Permission::MANAGER->value))) {
                $tournament = $this->tournamentRepository->update($id, $request->validated());
                return new TournamentResource($tournament);
            }
            throw new Exception('Unauthorized');
        } catch (Exception $e) {
            throw new Exception('Tournament update failed: ' . $e->getMessage(), 403);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            if ($user && ($user->hasPermissionTo(Permission::SUPER_ADMIN->value) ||
                $user->hasPermissionTo(Permission::MANAGER->value))) {
                $this->tournamentRepository->delete($id);
                return response()->json(['message' => 'Tournament deleted successfully'], 200);
            }
            throw new Exception('Unauthorized');
        } catch (Exception $e) {
            throw new Exception('Tournament deletion failed: ' . $e->getMessage(), 403);
        }
    }

    // Public: Tournament details with associated items and bundles
    public function details($id)
    {
        $tournament = $this->tournamentRepository->find($id);
        if (!$tournament || $tournament->status->value !== TournamentStatus::ACTIVE->value) {
            return response()->json(['message' => 'Tournament not found'], 404);
        }

        // Items: match ItemResource format (ensure strings for price)
        $items = $tournament->items->map(function ($item) {
            $override = $item->pivot?->price;
            return [
                'id' => (int) $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => number_format((float) ($override !== null ? $override : $item->price), 2, '.', ''),
                'stock' => (int) ($item->stock ?? 0),
                'image_url' => $item->image_url,
                'status' => $item->status->value,
            ];
        })->values();

        // Ensure bundle items are loaded for consistent formatting
        $tournament->bundles->load('items');

        $bundles = $tournament->bundles->map(function ($bundle) {
            $override = $bundle->pivot?->price;
            $bundleItems = $bundle->items->map(function ($bi) {
                return [
                    'id' => (int) $bi->id,
                    'name' => $bi->name,
                    'quantity' => (int) ($bi->pivot->quantity ?? 1),
                    'price' => number_format((float) ($bi->price ?? 0), 2, '.', ''),
                ];
            })->values();

            $totalItems = $bundle->items->map(function ($bi) {
                return ($bi->pivot->quantity ?? 1) . ' ' . $bi->name;
            })->implode(', ');

            return [
                'id' => (int) $bundle->id,
                'name' => $bundle->name,
                'description' => $bundle->description,
                'image' => $bundle->image ? asset('storage/' . $bundle->image) : null,
                'price' => number_format((float) ($override !== null ? $override : $bundle->price), 2, '.', ''),
                'status' => $bundle->status->value,
                'items' => $bundleItems,
                'total_items' => $totalItems,
            ];
        })->values();

        return response()->json([
            'id' => $tournament->id,
            'sport_id' => $tournament->sport_id,
            'sport_name' => $tournament->sport?->name,
            'name' => $tournament->name,
            'image' => $tournament->image ? asset('storage/' . $tournament->image) : null,
            'start_date' => optional($tournament->start_date)->toDateString(),
            'end_date' => optional($tournament->end_date)->toDateString(),
            'location' => $tournament->location,
            'status' => $tournament->status->value,
            'items' => $items,
            'bundles' => $bundles,
        ]);
    }
}
