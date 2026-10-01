<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'id' => $this->id,
            'category_id' => $this->category_id,
            'title' => $this->title,
            'description' => $this->description,
            'venue' => $this->venue,
            'date' => $this->date,
            'capacity' => $this->capacity,
            'price' => $this->price,
            'tickets_sold' => $this->tickets_sold,
            'tickets_remaining' => $this->capacity - $this->tickets_sold,
            'price_formatted' => 'NGN ' . number_format($this->price / 100, 2),
            'banner_image' => $this->banner_image ? asset('storage/' . $this->banner_image) : null,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
