<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class SupportTicket extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSE_REQUESTED = 'close_requested';
    public const STATUS_CLOSED = 'closed';

    // Alias shown to clients for any staff response
    public const CLIENT_ALIAS = 'Cali Rowe';

    protected $fillable = [
        'reference_number',
        'client_id',
        'agent_id',
        'created_by',
        'subject',
        'slug',
        'status',
        'close_requested_by',
        'close_requested_at',
        'close_request_note',
        'closed_by',
        'closed_at',
        'last_message_at',
    ];

    protected $casts = [
        'close_requested_at' => 'datetime',
        'closed_at' => 'datetime',
        'last_message_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $ticket) {
            if (blank($ticket->reference_number)) {
                $ticket->reference_number = self::generateUniqueReferenceNumber();
            }

            if (blank($ticket->slug)) {
                $ticket->slug = self::makeUniqueSlug((string) $ticket->subject);
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closeRequester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'close_requested_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(SupportTicketMessage::class)->latestOfMany();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isAgent()) {
            return $query->where('agent_id', $user->id);
        }

        return $query->where('client_id', $user->id);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function isCloseRequested(): bool
    {
        return $this->status === self::STATUS_CLOSE_REQUESTED;
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function getRouteKey(): mixed
    {
        return $this->display_reference;
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $value = trim((string) $value);

        if (preg_match('/^st-(\d+)$/i', $value, $matches)) {
            $referenceNumber = (int) $matches[1];

            return $this->newQuery()
                ->where('reference_number', $referenceNumber)
                ->first()
                ?? $this->newQuery()->whereKey($referenceNumber)->first();
        }

        if (is_numeric($value)) {
            return $this->newQuery()->whereKey((int) $value)->first();
        }

        return $this->newQuery()->where('slug', $value)->first();
    }

    public function getDisplayReferenceAttribute(): string
    {
        if (filled($this->reference_number)) {
            return 'ST-' . $this->reference_number;
        }

        return 'ST-' . str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'Open',
            self::STATUS_CLOSE_REQUESTED => 'Close Requested',
            self::STATUS_CLOSED => 'Closed',
            default => ucfirst(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'badge bg-success',
            self::STATUS_CLOSE_REQUESTED => 'badge bg-warning text-dark',
            self::STATUS_CLOSED => 'badge bg-secondary',
            default => 'badge bg-secondary',
        };
    }

    public static function makeUniqueSlug(string $subject, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug(Str::limit(trim($subject), 120, ''));
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'support-ticket';
        $slug = $baseSlug;
        $suffix = 2;

        while (
            static::query()
                ->when($ignoreId !== null, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    public static function generateUniqueReferenceNumber(): int
    {
        do {
            $digits = random_int(5, 7);
            $minimum = 10 ** ($digits - 1);
            $maximum = (10 ** $digits) - 1;
            $referenceNumber = random_int($minimum, $maximum);
        } while (static::query()->where('reference_number', $referenceNumber)->exists());

        return $referenceNumber;
    }
}
