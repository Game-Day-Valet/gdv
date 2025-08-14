<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Http\Requests\SportRequest;
use App\Http\Resources\SportResource;
use App\Http\Resources\TournamentResource;
use App\Repositories\SportRepositoryInterface;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Exception;

class SportController extends Controller
{
    protected $sportRepository;

    public function __construct(SportRepositoryInterface $sportRepository)
    {
        $this->sportRepository = $sportRepository;
    }

    public function index()
    {
        $sports = $this->sportRepository->getAllActive();
        return SportResource::collection($sports);
    }

    public function store(SportRequest $request)
    {
        try {
            $user = Auth::user();
            if ($user && ($user->hasPermissionTo(Permission::SUPER_ADMIN->value) ||
                $user->hasPermissionTo(Permission::MANAGER->value))) {
                $sport = $this->sportRepository->create($request->validated());
                return new SportResource($sport);
            }
            throw new Exception('Unauthorized');
        } catch (Exception $e) {
            throw new Exception('Sport creation failed: ' . $e->getMessage(), 403);
        }
    }

    public function update(SportRequest $request, $id)
    {
        try {
            $user = Auth::user();
            if ($user && ($user->hasPermissionTo(Permission::SUPER_ADMIN->value) ||
                $user->hasPermissionTo(Permission::MANAGER->value))) {
                $sport = $this->sportRepository->update($id, $request->validated());
                return new SportResource($sport);
            }
            throw new Exception('Unauthorized');
        } catch (Exception $e) {
            throw new Exception('Sport update failed: ' . $e->getMessage(), 403);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if ($user && ($user->hasPermissionTo(Permission::SUPER_ADMIN->value) ||
                $user->hasPermissionTo(Permission::MANAGER->value))) {
                $this->sportRepository->delete($id);
                return response()->json(['message' => 'Sport deleted successfully'], 200);
            }
            throw new Exception('Unauthorized');
        } catch (Exception $e) {
            throw new Exception('Sport deletion failed: ' . $e->getMessage(), 403);
        }
    }

    public function tournaments(Request $request, $id)
    {
        $tournaments = $this->sportRepository->getTournamentsBySport($id, true, $request->limit ?? 10);
        return TournamentResource::collection($tournaments);
    }
}
