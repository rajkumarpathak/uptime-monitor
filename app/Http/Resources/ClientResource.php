<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'website_count' => $this->whenLoaded('websites', function () {
                return $this->websites->count();
            }, $this->websites_count ?? 0),
            'active_website_count' => $this->whenLoaded('websites', function () {
                return $this->websites->where('status', '!=', 'inactive')->count();
            }),
            'websites' => WebsiteResource::collection($this->whenLoaded('websites')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'created_at_human' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}