<?php

namespace App\Models;

use App\Models\Concerns\HasSlugRouteKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientSalesPopup extends Model
{
    use HasFactory;
    use HasSlugRouteKey;

    public const TARGET_RECURRING = 'recurring';
    public const TARGET_SPECIFIC = 'specific';

    protected $fillable = [
        'title',
        'badge_text',
        'message',
        'price_text',
        'cta_label',
        'cta_link',
        'image_path',
        'image_url',
        'bg_color',
        'text_color',
        'accent_color',
        'target_type',
        'target_client_id',
        'show_delay',
        'starts_at',
        'ends_at',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_delay' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public static function targetTypeOptions(): array
    {
        return [
            self::TARGET_RECURRING => 'Recurring Clients',
            self::TARGET_SPECIFIC => 'Specific Client',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_client_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query
            ->where(function (Builder $inner): void {
                $inner->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $inner): void {
                $inner->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });
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

    protected function routeKeyPrefix(): string
    {
        return 'cp';
    }

    protected function routeKeySourceColumn(): ?string
    {
        return 'title';
    }
}
