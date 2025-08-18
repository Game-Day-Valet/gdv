<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    protected $isCart;

    public function __construct($resource, $isCart = false)
    {
        parent::__construct($resource);
        $this->isCart = $isCart;
    }

    public function toArray($request, $isCart = false)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => (int) $this->stock,
            'image_url' => $this->image_url,
            // 'availability' => $this->availability,
            'status' => $this->status->value,
        ];
        if ($this->isCart) {
            $data['quantity'] = $this->cart_items->sum('quantity');
        }
        return $data;
    }
}
