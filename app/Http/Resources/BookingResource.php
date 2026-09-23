<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return    [
            'id' => $this->id,
        'quantity' => $this->quantity,
        'total_price' => $this->total_price,
        'status' => $this->status,
        'event' => new EventResource($this->whenLoaded('event')),
        'user' => new UserResource($this->whenLoaded('user')),
        'created_at' => $this->created_at,
        ];
}}
