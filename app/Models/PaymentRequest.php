<?php

namespace App\Models;

use App\Models\Concerns\HasSlugRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PaymentRequest extends Model
{
    use HasFactory, HasSlugRouteKey;

    const STATUS_PENDING = 'pending';
    const STATUS_CLIENT_MARKED = 'client_marked_paid';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'reference_number',
        'client_id',
        'requested_by',
        'amount',
        'note',
        'payment_link',
        'payment_proof_path',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
        'client_marked_at',
        'payment_proof_uploaded_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'client_marked_at' => 'datetime',
        'payment_proof_uploaded_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $paymentRequest) {
            if (blank($paymentRequest->reference_number)) {
                $paymentRequest->reference_number = self::generateUniqueReferenceNumber();
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public static function generateUniqueReferenceNumber(): int
    {
        do {
            $referenceNumber = random_int(100000, 999999);
        } while (self::where('reference_number', $referenceNumber)->exists());

        return $referenceNumber;
    }

    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_PENDING
            && !$this->isCancelled()
            && $this->rejected_at !== null
            && filled($this->rejection_reason);
    }

    public function isPlainPending(): bool
    {
        return $this->status === self::STATUS_PENDING
            && !$this->isRejected()
            && !$this->isCancelled();
    }

    public function canBeRejected(): bool
    {
        return !$this->isCancelled() && $this->status === self::STATUS_CLIENT_MARKED;
    }

    public function canBeCancelled(): bool
    {
        return !$this->isCancelled() && $this->status !== self::STATUS_APPROVED;
    }

    public function getDisplayReferenceAttribute(): string
    {
        return '#' . ($this->reference_number ?: $this->id);
    }

    public function hasPaymentProof(): bool
    {
        return filled($this->payment_proof_path);
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        if (!$this->hasPaymentProof()) {
            return null;
        }

        return Storage::disk('public')->url($this->payment_proof_path);
    }

    public function getDisplayStatusLabel(): string
    {
        if ($this->isCancelled()) {
            return 'Cancelled';
        }

        if ($this->status === self::STATUS_APPROVED) {
            return 'Approved';
        }

        if ($this->status === self::STATUS_CLIENT_MARKED) {
            return 'Client Marked Paid';
        }

        if ($this->isRejected()) {
            return 'Rejected';
        }

        return 'Pending';
    }

    public function getStatusBadgeClass(): string
    {
        if ($this->isCancelled()) {
            return 'bg-dark text-white';
        }

        if ($this->status === self::STATUS_APPROVED) {
            return 'bg-success';
        }

        if ($this->status === self::STATUS_CLIENT_MARKED) {
            return 'bg-info text-dark';
        }

        if ($this->isRejected()) {
            return 'bg-danger';
        }

        return 'bg-warning text-dark';
    }

    protected function routeKeyPrefix(): string
    {
        return 'pr';
    }

    protected function routeKeySourceColumn(): ?string
    {
        return 'reference_number';
    }
}
