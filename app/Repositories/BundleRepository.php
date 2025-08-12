<?php

namespace App\Repositories;

use App\Models\Bundle;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use App\Enums\ItemStatus;

class BundleRepository implements BundleRepositoryInterface
{
    public function getAllAvailable()
    {
        return Bundle::where('status', ItemStatus::AVAILABLE->value)
            ->with('items', 'cart_items')
            ->get();
    }

    public function find($id)
    {
        return Bundle::with('items')->findOrFail($id);
    }

    public function create(array $data, array $items)
    {
        return DB::transaction(function () use ($data, $items) {
            $bundle = Bundle::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'status' => $data['status'] ?? ItemStatus::AVAILABLE->value,
            ]);

            $this->syncItems($bundle, $items);

            return $bundle;
        });
    }

    public function update($id, array $data, array $items)
    {
        return DB::transaction(function () use ($id, $data, $items) {
            $bundle = Bundle::findOrFail($id);
            $bundle->update([
                'name' => $data['name'] ?? $bundle->name,
                'description' => $data['description'] ?? $bundle->description,
                'price' => $data['price'] ?? $bundle->price,
                'status' => $data['status'] ?? $bundle->status,
            ]);

            $this->syncItems($bundle, $items);

            return $bundle;
        });
    }

    public function delete($id)
    {
        $bundle = Bundle::findOrFail($id);
        $bundle->delete();
    }

    protected function syncItems($bundle, array $items)
    {
        $syncData = [];
        foreach ($items as $item) {
            $itemModel = Item::findOrFail($item['item_id']);
            if ($itemModel->status !== ItemStatus::AVAILABLE) {
                throw new \Exception("Item {$itemModel->name} is unavailable");
            }
            if ($itemModel->stock < $item['quantity']) {
                throw new \Exception("Insufficient stock for item {$itemModel->name}");
            }
            $syncData[$item['item_id']] = ['quantity' => $item['quantity']];
        }
        $bundle->items()->sync($syncData);
    }
}
