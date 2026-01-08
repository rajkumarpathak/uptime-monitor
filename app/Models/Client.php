<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_notified_at' => 'datetime',
    ];

    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
    }

    public function activeWebsites(): HasMany
    {
        return $this->websites()->where('status', '!=', 'inactive');
    }

    public function hasReachedWebsiteLimit(): bool
    {
        return $this->activeWebsites()->count() >= 10;
    }
}