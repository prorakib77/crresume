<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Searchable;

class ClientProfile extends Model
{
    use HasFactory, Searchable;

    public const ONBOARDING_STATUS_PENDING = 'pending';
    public const ONBOARDING_STATUS_COMPLETED = 'completed';
    public const ONBOARDING_STATUS_REQUESTED_AGAIN = 'requested_again';

    public const SERVICE_TYPE_REGULAR = 'regular';
    public const SERVICE_TYPE_VIP = 'vip';

    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'resume',
        'onboarding_file',
        'service_start_date',
        'service_end_date',
        'status',
        'apply_to',
        'onboarding_resume_file',
        'onboarding_form_file',
        'onboarding_text',
        'onboarding_note',
        'onboarding_submitted_at',
        'onboarding_requested_at',
        'onboarding_visible',
        'onboarding_status',
        'estimated_resume_completion_date',
        'estimated_cover_letter_completion_date',
        'estimated_application_start_date',
        'service_package',
        'service_type',
        'client_signature_path',
        'policy_acknowledged_at',
    ];

    protected $casts = [
        'service_start_date' => 'date',
        'service_end_date' => 'date',
        'onboarding_submitted_at' => 'datetime',
        'onboarding_requested_at' => 'datetime',
        'onboarding_visible' => 'boolean',
        'estimated_resume_completion_date' => 'date',
        'estimated_cover_letter_completion_date' => 'date',
        'estimated_application_start_date' => 'date',
        'policy_acknowledged_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // Each client profile can be assigned to many agents
    public function agents()
    {
        return $this->belongsToMany(
            User::class,
            'agent_client',
            'client_id', // client_profiles.id
            'agent_id'   // users.id
        )->where('role_id', 2)->withTimestamps();
    }

    public function resolvedOnboardingStatus(): string
    {
        if (in_array($this->onboarding_status, [
            self::ONBOARDING_STATUS_PENDING,
            self::ONBOARDING_STATUS_COMPLETED,
            self::ONBOARDING_STATUS_REQUESTED_AGAIN,
        ], true)) {
            return $this->onboarding_status;
        }

        if ($this->onboarding_visible) {
            return $this->hasSubmittedOnboarding()
                ? self::ONBOARDING_STATUS_REQUESTED_AGAIN
                : self::ONBOARDING_STATUS_PENDING;
        }

        return $this->hasSubmittedOnboarding()
            ? self::ONBOARDING_STATUS_COMPLETED
            : self::ONBOARDING_STATUS_PENDING;
    }

    public function hasSubmittedOnboarding(): bool
    {
        return filled($this->onboarding_submitted_at)
            || filled($this->onboarding_text)
            || filled($this->onboarding_resume_file)
            || filled($this->onboarding_form_file)
            || filled($this->client_signature_path);
    }

    public function shouldShowOnboardingForm(): bool
    {
        return $this->resolvedOnboardingStatus() !== self::ONBOARDING_STATUS_COMPLETED;
    }

    public function onboardingStatusLabel(): string
    {
        return match ($this->resolvedOnboardingStatus()) {
            self::ONBOARDING_STATUS_COMPLETED => 'Completed',
            self::ONBOARDING_STATUS_REQUESTED_AGAIN => 'Requested Again',
            default => 'Pending',
        };
    }

    public function serviceTypeLabel(): string
    {
        return match ($this->service_type) {
            self::SERVICE_TYPE_VIP => 'VIP',
            default => 'Regular',
        };
    }


}
