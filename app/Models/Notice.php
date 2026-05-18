<?php

namespace App\Models;

use App\Models\Concerns\HasSlugRouteKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notice extends Model
{
    use HasFactory, HasSlugRouteKey;

    public const AUDIENCE_AGENT = 'agent';
    public const AUDIENCE_CLIENT = 'client';
    public const AUDIENCE_BOTH = 'both';

    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_PAYMENT_REQUEST = 'payment_request';
    public const SOURCE_ONBOARDING_REQUEST = 'onboarding_request';
    public const SOURCE_SERVICE_STATUS = 'service_status';

    protected $fillable = [
        'title',
        'content',
        'icon_class',
        'background_color',
        'audience',
        'recipient_user_id',
        'created_by',
        'source_type',
        'source_id',
        'action_url',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dismissals(): HasMany
    {
        return $this->hasMany(NoticeDismissal::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $activeQuery) {
                $activeQuery->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $activeQuery) {
                $activeQuery->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });
    }

    public function scopeVisibleToUser(Builder $query, User $user): Builder
    {
        $allowedAudiences = match (true) {
            $user->isAgent() => [self::AUDIENCE_AGENT, self::AUDIENCE_BOTH],
            $user->isClient() => [self::AUDIENCE_CLIENT, self::AUDIENCE_BOTH],
            default => [],
        };

        if ($allowedAudiences === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->whereIn('audience', $allowedAudiences)
            ->where(function (Builder $visibilityQuery) use ($user) {
                $visibilityQuery->where('recipient_user_id', $user->id)
                    ->orWhereNull('recipient_user_id');
            });
    }

    public function scopeNotDismissedFor(Builder $query, User $user): Builder
    {
        return $query->whereDoesntHave('dismissals', function (Builder $dismissalQuery) use ($user) {
            $dismissalQuery->where('user_id', $user->id)
                ->where('dismissed_until', '>', now());
        });
    }

    public function getResolvedIconClassAttribute(): string
    {
        return filled($this->icon_class)
            ? $this->icon_class
            : 'fa-solid fa-circle-info';
    }

    public function getResolvedTextColorAttribute(): string
    {
        $hex = ltrim((string) $this->background_color, '#');

        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return '#111111';
        }

        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));
        $luminance = (($red * 299) + ($green * 587) + ($blue * 114)) / 1000;

        return $luminance >= 155 ? '#111111' : '#ffffff';
    }

    public function getTargetLabelAttribute(): string
    {
        if ($this->recipient) {
            return $this->recipient->name;
        }

        return match ($this->audience) {
            self::AUDIENCE_AGENT => 'All Agents',
            self::AUDIENCE_CLIENT => 'All Clients',
            default => 'Agents & Clients',
        };
    }

    public function getResolvedActionLabelAttribute(): string
    {
        return match ($this->source_type) {
            self::SOURCE_SERVICE_STATUS => 'Contact Us',
            self::SOURCE_ONBOARDING_REQUEST => 'Submit Onboarding',
            self::SOURCE_PAYMENT_REQUEST => 'Open Dashboard',
            default => 'Open',
        };
    }

    protected function routeKeyPrefix(): string
    {
        return 'n';
    }

    protected function routeKeySourceColumn(): ?string
    {
        return 'title';
    }
}
