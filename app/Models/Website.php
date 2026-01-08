<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Website extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'url',
        'status',
        'last_checked_at',
        'last_down_at',
        'check_count',
        'failure_count',
        'last_error',
    ];

    protected $casts = [
        'last_checked_at' => 'datetime',
        'last_down_at' => 'datetime',
    ];

    // Append these computed attributes to the model's array/JSON
    protected $appends = ['status_display', 'status_color', 'uptime_percentage'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function markAsUp(): void
    {
        $this->update([
            'status' => 'up',
            'last_checked_at' => now(),
            'check_count' => $this->check_count + 1,
        ]);
    }

    public function markAsDown(string $error = null): void
    {
        $this->update([
            'status' => 'down',
            'last_checked_at' => now(),
            'last_down_at' => now(),
            'check_count' => $this->check_count + 1,
            'failure_count' => $this->failure_count + 1,
            'last_error' => $error,
        ]);
    }

    public function getNormalizedUrl(): string
    {
        $url = $this->url;
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'http://' . $url;
        }
        return rtrim($url, '/');
    }

    /**
     * Get display text for status
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'up' => 'Up',
            'down' => 'Down',
            'checking' => 'Checking',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get color for status (for UI display)
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'up' => 'green',
            'down' => 'red',
            'checking' => 'yellow',
            default => 'gray',
        };
    }

    /**
     * Get CSS class for status (for Tailwind/Bootstrap)
     */
    public function getStatusClassAttribute(): string
    {
        return match($this->status) {
            'up' => 'bg-green-100 text-green-800',
            'down' => 'bg-red-100 text-red-800',
            'checking' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get badge icon for status
     */
    public function getStatusIconAttribute(): string
    {
        return match($this->status) {
            'up' => '✓',
            'down' => '✗',
            'checking' => '⟳',
            default => '?',
        };
    }

    /**
     * Calculate uptime percentage
     */
    public function getUptimePercentageAttribute(): float
    {
        if ($this->check_count === 0) {
            return 0.0;
        }
        
        $successfulChecks = $this->check_count - $this->failure_count;
        return round(($successfulChecks / $this->check_count) * 100, 2);
    }

    /**
     * Check if website is currently down
     */
    public function isDown(): bool
    {
        return $this->status === 'down';
    }

    /**
     * Check if website is currently up
     */
    public function isUp(): bool
    {
        return $this->status === 'up';
    }

    /**
     * Check if website needs to be checked (not checked in the last 15 minutes)
     */
    public function needsChecking(): bool
    {
        if (is_null($this->last_checked_at)) {
            return true;
        }
        
        return $this->last_checked_at->diffInMinutes(now()) >= 15;
    }

    /**
     * Get formatted last checked time
     */
    public function getLastCheckedHumanAttribute(): string
    {
        if (is_null($this->last_checked_at)) {
            return 'Never';
        }
        
        return $this->last_checked_at->diffForHumans();
    }

    /**
     * Get formatted last down time
     */
    public function getLastDownHumanAttribute(): string
    {
        if (is_null($this->last_down_at)) {
            return 'Never';
        }
        
        return $this->last_down_at->diffForHumans();
    }

    /**
     * Scope: Get websites that need checking
     */
    public function scopeNeedsChecking($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('last_checked_at')
              ->orWhere('last_checked_at', '<', now()->subMinutes(15));
        });
    }

    /**
     * Scope: Get only active websites (not checking or up/down but active)
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'inactive');
    }

    /**
     * Scope: Get websites by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}