<?php

namespace App\Models;

use App\Models\Concerns\HasSlugRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class OtpVerification extends Model
{
    use HasFactory, HasSlugRouteKey;

    protected $fillable = [
        'agent_id',
        'client_id',
        'otp_code',
        'is_verified',
        'expires_at',
        'verified_at',
        'message'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->is_verified && !$this->isExpired();
    }

    public static function generateOtp(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function createForClient(
        int $agentId,
        int $clientId,
        ?string $message = null,
        ?int $expiresInMinutes = null
    ): self
    {
        // Deactivate any existing OTPs for this client
        self::where('client_id', $clientId)
            ->where('is_verified', false)
            ->update(['is_verified' => true]);

        $ttlMinutes = (int) ($expiresInMinutes ?? 10);
        if ($ttlMinutes < 1) {
            $ttlMinutes = 1;
        }
        if ($ttlMinutes > 10080) {
            $ttlMinutes = 10080;
        }

        return self::create([
            'agent_id' => $agentId,
            'client_id' => $clientId,
            'otp_code' => self::generateOtp(),
            'expires_at' => Carbon::now()->addMinutes($ttlMinutes),
            'message' => $message
        ]);
    }

    protected function routeKeyPrefix(): string
    {
        return 'ov';
    }
}
