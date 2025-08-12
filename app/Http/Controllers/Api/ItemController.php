<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permission;
use App\Http\Requests\ItemRequest;
use App\Http\Resources\ItemResource;
use App\Repositories\ItemRepositoryInterface;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;

class ItemController extends Controller
{
    protected $itemRepository;

    public function __construct(ItemRepositoryInterface $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    public function index(Request $request)
    {
        $items = $this->itemRepository->getAllAvailable($request->search, true, $request->limit);
        return ItemResource::collection($items);
    }

    public function store(ItemRequest $request)
    {
        try {
            $user = $request->user();
            if ($user && ($user->hasPermissionTo(Permission::SUPER_ADMIN->value) ||
                $user->hasPermissionTo(Permission::MANAGER->value))) {
                $item = $this->itemRepository->create($request->validated());
                return new ItemResource($item);
            }
            throw new Exception('Unauthorized');
        } catch (Exception $e) {
            throw new Exception('Item creation failed: ' . $e->getMessage(), 403);
        }
    }

    public function update(ItemRequest $request, $id)
    {
        try {
            $user = $request->user();
            if ($user && ($user->hasPermissionTo(Permission::SUPER_ADMIN->value) ||
                $user->hasPermissionTo(Permission::MANAGER->value))) {
                $item = $this->itemRepository->update($id, $request->validated());
                return new ItemResource($item);
            }
            throw new Exception('Unauthorized');
        } catch (Exception $e) {
            throw new Exception('Item update failed: ' . $e->getMessage(), 403);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            if ($user && ($user->hasPermissionTo(Permission::SUPER_ADMIN->value) ||
                $user->hasPermissionTo(Permission::MANAGER->value))) {
                $this->itemRepository->delete($id);
                return response()->json(['message' => 'Item deleted successfully'], 200);
            }
            throw new Exception('Unauthorized');
        } catch (Exception $e) {
            throw new Exception('Item deletion failed: ' . $e->getMessage(), 403);
        }
    }
}
