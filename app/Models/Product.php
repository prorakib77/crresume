<?php

namespace App\Models;

use App\Models\Concerns\HasSlugRouteKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    use HasSlugRouteKey;

    public const TYPE_FULL_SERVICE = 'full_service';
    public const TYPE_COACHING = 'coaching';
    public const TYPE_DIGITAL_PRODUCT = 'digital_product';

    protected $fillable = [
        'type',
        'title',
        'badge_text',
        'regular_price',
        'sale_price',
        'cta_label',
        'cta_link',
        'image_path',
        'image_url',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'regular_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForType(Builder $query, ?string $type): Builder
    {
        if (blank($type)) {
            return $query;
        }

        return $query->where('type', $type);
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

    public static function typeOptions(): array
    {
        return [
            self::TYPE_FULL_SERVICE => 'Full Service',
            self::TYPE_COACHING => 'Coaching',
            self::TYPE_DIGITAL_PRODUCT => 'Digital Product',
        ];
    }

    protected function routeKeyPrefix(): string
    {
        return 'p';
    }

    protected function routeKeySourceColumn(): ?string
    {
        return 'title';
    }
}
