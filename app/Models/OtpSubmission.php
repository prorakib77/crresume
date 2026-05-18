<?php

namespace App\Models;

use App\Models\Concerns\HasSlugRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpSubmission extends Model
{
    use HasFactory, HasSlugRouteKey;

    protected $fillable = [
        'otp_verification_id',
        'agent_id',
        'client_id',
        'company_name',
        'otp_code',
        'status',
        'notes',
        'submitted_at',
        'reviewed_at'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function otpVerification(): BelongsTo
    {
        return $this->belongsTo(OtpVerification::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'pending' => 'badge-warning',
            'reviewed' => 'badge-info',
            'approved' => 'badge-success',
            'processed' => 'badge-success',
            'rejected' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pending Review',
            'reviewed' => 'Reviewed',
            'approved' => 'Approved',
            'processed' => 'Processed',
            'rejected' => 'Rejected',
            default => 'Unknown',
        };
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessed(): bool
    {
        return in_array($this->status, ['processed', 'reviewed', 'approved']);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    protected function routeKeyPrefix(): string
    {
        return 'os';
    }

    protected function routeKeySourceColumn(): ?string
    {
        return 'company_name';
    }
}
