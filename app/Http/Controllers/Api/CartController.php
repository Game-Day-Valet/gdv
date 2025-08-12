<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CartItemRequest;
use App\Http\Resources\BundleResource;
use App\Http\Resources\CartItemResource;
use App\Http\Resources\ItemResource;
use App\Models\Bundle;
use App\Models\CartItem;
use App\Models\Item;
use App\Repositories\ItemRepositoryInterface;
use App\Repositories\BundleRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected $itemRepository, $bundleRepository;

    public function __construct(ItemRepositoryInterface $itemRepository, BundleRepositoryInterface $bundleRepository)
    {
        $this->itemRepository = $itemRepository;
        $this->bundleRepository = $bundleRepository;
    }
    public function index()
    {
        $user = Auth::user();
        $items = $this->itemRepository->getAllAvailable();
        $itemsWithCart = ItemResource::collection($items, true);
        $itemsWithCart = ItemResource::collection(
            $items->map(fn($item) => new ItemResource($item, true))
        );
        $bundles = $this->bundleRepository->getAllAvailable();
        $bundlesWithCart = BundleResource::collection(
            $bundles->map(fn($bundle) => new BundleResource($bundle, true))
        );
          $data['items'] = $itemsWithCart;
        $data['bundles'] = $bundlesWithCart;
        return $data;
     }

    public function store(CartItemRequest $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                throw new Exception('Unauthorized');
            }

            $data = $request->validated();
            $isBundle = (bool) $data['is_bundle'];
            $itemId = (int) $data['item_id'];
            $quantity = (int) $data['quantity'];

            // Ensure referenced entity exists and derive unit_price if not provided
            if ($isBundle) {
                $bundle = Bundle::findOrFail($itemId);
                $unitPrice = $data['unit_price'] ?? $bundle->price;
            } else {
                $item = Item::findOrFail($itemId);
                $unitPrice = $data['unit_price'] ?? $item->price;
            }

            $totalPrice = (float) $unitPrice * $quantity;

            $cartItem = CartItem::create([
                'user_id' => $user->id,
                'item_id' => $itemId,
                'is_bundle' => $isBundle,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ]);

            return new CartItemResource($cartItem);
        } catch (Exception $e) {
            throw new Exception('Cart item store failed: ' . $e->getMessage(), 403);
        }
    }
}


