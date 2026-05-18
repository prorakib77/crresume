<?php

namespace App\Models;

use App\Models\Concerns\HasSlugRouteKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;
    use HasSlugRouteKey;

    protected $fillable = [
        'customer_name',
        'country_label',
        'headline',
        'review_text',
        'product_name',
        'product_link',
        'before_image_path',
        'before_image_url',
        'after_image_path',
        'after_image_url',
        'is_verified',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getBeforeImageSourceUrlAttribute(): ?string
    {
        if (filled($this->before_image_url)) {
            return $this->before_image_url;
        }

        if (blank($this->before_image_path)) {
            return null;
        }

        return storage_public_url((string) $this->before_image_path);
    }

    public function getAfterImageSourceUrlAttribute(): ?string
    {
        if (filled($this->after_image_url)) {
            return $this->after_image_url;
        }

        if (blank($this->after_image_path)) {
            return null;
        }

        return storage_public_url((string) $this->after_image_path);
    }

    public function getImageSourceUrlAttribute(): ?string
    {
        return $this->before_image_source_url ?: $this->after_image_source_url;
    }

    protected function routeKeyPrefix(): string
    {
        return 'r';
    }

    protected function routeKeySourceColumn(): ?string
    {
        return 'customer_name';
    }
}
