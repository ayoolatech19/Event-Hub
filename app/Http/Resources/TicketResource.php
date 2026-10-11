<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
public function toArray(Request $request): array
{
    return [
        'code' => $this->code,
        'status' => $this->status,
        'qr_url' => $this->qr_path ? Storage::url($this->qr_path) : null,
        'checked_in_at' => $this->checked_in_at,
    ];
}
    }

