<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCloudResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_code' => $this->product_code,
            'productName'  => $this->product_name,
            'product_url_image' => $this->product_url_image,
            'product_description' => $this->product_description,
            'product_price' => $this->product_price,
            'category_id' => $this->category_id,
        ];
    }
}
