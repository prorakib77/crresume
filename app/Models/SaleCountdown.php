<?php

namespace App\Models;

use App\Models\Concerns\HasSlugRouteKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SaleCountdown extends Model
{
    use HasFactory;
    use HasSlugRouteKey;

    protected $fillable = [
        'title',
        'badge_text',
        'subtitle',
        'offer_text',
        'end_at',
        'cta_label',
        'cta_link',
        'image_path',
        'image_url',
        'bg_color',
        'text_color',
        'accent_color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'end_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeActiveAndLive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('end_at', '>', now());
    }

    public function getImageSourceUrlAttribute(): ?string
    {
        if (filled($this->image_url)) {
            return $this->image_url;
        }

        if (blank($this->image_path)) {
            return null;
        }

        return storage_public_url((string) $this->image_path);
    }

    public function getHasEndedAttribute(): bool
    {
        if (!$this->end_at instanceof Carbon) {
            return true;
        }

        return $this->end_at->isPast();
    }

    protected function routeKeyPrefix(): string
    {
        return 'cd';
    }

    protected function routeKeySourceColumn(): ?string
    {
        return 'title';
    }
}
