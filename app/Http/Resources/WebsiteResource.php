<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebsiteResource extends JsonResource
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
            'client_id' => $this->client_id,
            'url' => $this->url,
            'status' => $this->status,
            'status_display' => $this->getStatusDisplay(), // Access model method
            'status_color' => $this->getStatusColor(),     // Access model method
            'last_checked_at' => $this->last_checked_at?->format('Y-m-d H:i:s'),
            'last_checked_at_human' => $this->last_checked_at?->diffForHumans(),
            'last_down_at' => $this->last_down_at?->format('Y-m-d H:i:s'),
            'check_count' => $this->check_count,
            'failure_count' => $this->failure_count,
            'uptime_percentage' => $this->getUptimePercentage(), // Access model method
            'last_error' => $this->last_error,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get display text for status
     */
    public function getStatusDisplay(): string
    {
        return match($this->status) {
            'up' => 'Up',
            'down' => 'Down',
            'checking' => 'Checking',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get color for status
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'up' => 'green',
            'down' => 'red',
            'checking' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Calculate uptime percentage
     */
    public function getUptimePercentage(): float
    {
        if ($this->check_count === 0) {
            return 0.0;
        }
        
        $successfulChecks = $this->check_count - $this->failure_count;
        return round(($successfulChecks / $this->check_count) * 100, 2);
    }
}