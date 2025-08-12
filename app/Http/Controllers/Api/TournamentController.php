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
}
