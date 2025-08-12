<?php

namespace App\Repositories;

use App\Models\Item;
use Illuminate\Support\Facades\DB;
use App\Enums\ItemStatus;
use Illuminate\Support\Facades\Storage;

class ItemRepository implements ItemRepositoryInterface
{
    public function getAllAvailable($search = '', $pagination = false, $limit = 10)
    {
        $query = Item::with('cart_items')->where('status', ItemStatus::AVAILABLE->value)
        ->when($search, function ($query, $search) {
            $query->where('name', 'like', "%$search%");
        });
        if ($pagination === true) {
            return $query->paginate($limit);
        }
        return $query->get();
    }

    public function find($id)
    {
        return Item::findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $imagePath = null;
            if (isset($data['image']) && $data['image']) {
                // Store image in storage/app/public/items
                $imagePath = $data['image']->store('items', 'public');
            }

            return Item::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'],
                'stock' => $data['stock'] ?? null,
                'image_path' => $imagePath, // Store relative path
                'availability' => $data['availability'] ?? null,
                'status' => $data['status'] ?? ItemStatus::AVAILABLE->value,
            ]);
        });
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $item = Item::findOrFail($id);

            $imagePath = $item->image_path;
            if (isset($data['image']) && $data['image']) {
                // Delete old image if it exists
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
                // Store new image
                $imagePath = $data['image']->store('items', 'public');
            } elseif (array_key_exists('image', $data) && is_null($data['image'])) {
                // If image is explicitly set to null, delete the existing image
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
                $imagePath = null;
            }

            $item->update([
                'name' => $data['name'] ?? $item->name,
                'description' => $data['description'] ?? $item->description,
                'price' => $data['price'] ?? $item->price,
                'stock' => $data['stock'] ?? $item->stock,
                'image_path' => $imagePath,
                'availability' => $data['availability'] ?? $item->availability,
                'status' => $data['status'] ?? $item->status,
            ]);

            return $item;
        });
    }

    public function delete($id)
    {
        $item = Item::findOrFail($id);
        // Delete image from storage if it exists
        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }
        $item->delete();
    }

    public function checkAvailability($id, $date, $quantity)
    {
        $item = Item::findOrFail($id);
        if ($item->status !== ItemStatus::AVAILABLE->value) {
            throw new \Exception("Item is unavailable");
        }

        if ($item->availability && isset($item->availability[$date])) {
            if ($item->availability[$date] < $quantity) {
                throw new \Exception("Insufficient stock for $date");
            }
        } elseif ($item->stock < $quantity) {
            throw new \Exception("Insufficient total stock");
        }
        return true;
    }
}
