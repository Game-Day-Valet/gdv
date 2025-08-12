<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BundleResource extends JsonResource
{
    protected $isCart;

    public function __construct($resource, $isCart = false)
    {
        parent::__construct($resource);
        $this->isCart = $isCart;
    }

    public function toArray($request)
    {
        $totalItems = $this->items->map(function($item) {
            return $item->pivot->quantity.' '.$item->name;
        })->implode(', ');

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'status' => $this->status->value,
            'items' => $this->whenLoaded('items', fn() => $this->items->map(fn($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'quantity' => $item->pivot->quantity,
                'price' => $item->price,
            ])),
            'total_items' => $totalItems,
        ];

        if ($this->isCart) {
            $data['quantity'] = $this->cart_items->sum('quantity');
        }

        return $data;
    }
}
