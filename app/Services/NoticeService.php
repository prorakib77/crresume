<?php

namespace App\Services;

use App\Models\AgentClientAssignment;
use App\Models\ClientProfile;
use App\Models\Notice;
use App\Models\NoticeDismissal;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NoticeService
{
    public function getDashboardNotices(User $user, int $limit = 4): Collection
    {
        return $this->baseVisibleQueryWithoutDismissals($user)
            ->where(function ($query) {
                $query->whereNull('source_type')
                    ->orWhere('source_type', Notice::SOURCE_MANUAL)
                    ->orWhere('source_type', Notice::SOURCE_ONBOARDING_REQUEST)
                    ->orWhere('source_type', Notice::SOURCE_SERVICE_STATUS);
            })
            ->orderByRaw(
                "case when source_type = ? then 0 when source_type = ? then 1 else 2 end",
                [Notice::SOURCE_SERVICE_STATUS, Notice::SOURCE_ONBOARDING_REQUEST]
            )
            ->latest('updated_at')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public function getVisibleNoticesPaginated(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->baseVisibleQueryWithoutDismissals($user)
            ->latest()
            ->paginate($perPage);
    }

    public function getVisibleNoticeCount(User $user): int
    {
        return $this->baseVisibleQueryWithoutDismissals($user)->count();
    }

    public function dismissForUser(Notice $notice, User $user, int $hours = 12): NoticeDismissal
    {
        return NoticeDismissal::updateOrCreate(
            [
                'notice_id' => $notice->id,
                'user_id' => $user->id,
            ],
            [
                'dismissed_until' => now()->addHours($hours),
            ]
        );
    }

    public function createManualNotice(array $attributes, User $creator): Notice
    {
        return Notice::create([
            'title' => $attributes['title'],
            'content' => $attributes['content'],
            'icon_class' => $attributes['icon_class'] ?: 'fa-solid fa-bell',
            'background_color' => $attributes['background_color'] ?: '#fff7e0',
            'audience' => $attributes['audience'],
            'recipient_user_id' => $attributes['recipient_user_id'] ?? null,
            'created_by' => $creator->id,
            'source_type' => Notice::SOURCE_MANUAL,
            'source_id' => null,
            'action_url' => $attributes['action_url'] ?? null,
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => null,
        ]);
    }

    public function syncPaymentRequestNotice(PaymentRequest $paymentRequest): ?Notice
    {
        $client = $paymentRequest->client;

        if (!$client) {
            return null;
        }

        $notice = Notice::firstOrNew([
            'source_type' => Notice::SOURCE_PAYMENT_REQUEST,
            'source_id' => $paymentRequest->id,
            'recipient_user_id' => $client->id,
        ]);

        $endsAt = null;
        $iconClass = 'fa-solid fa-wallet';
        $backgroundColor = '#fff7e0';
        $title = 'Payment Request ' . $paymentRequest->display_reference;
        $content = 'A payment of $' . number_format((float) $paymentRequest->amount, 2) . ' is waiting for your review.';

        if ($paymentRequest->status === PaymentRequest::STATUS_CLIENT_MARKED) {
            $iconClass = 'fa-solid fa-receipt';
            $backgroundColor = '#eef6ff';
            $content = 'Payment ' . $paymentRequest->display_reference . ' was marked as paid and is waiting for admin review.';
        } elseif ($paymentRequest->isRejected()) {
            $iconClass = 'fa-solid fa-circle-exclamation';
            $backgroundColor = '#fff0f0';
            $content = 'Payment ' . $paymentRequest->display_reference . ' was rejected. Reason: ' . $paymentRequest->rejection_reason;
        } elseif ($paymentRequest->status === PaymentRequest::STATUS_APPROVED) {
            $iconClass = 'fa-solid fa-circle-check';
            $backgroundColor = '#ecfdf3';
            $title = 'Payment Approved ' . $paymentRequest->display_reference;
            $content = 'Payment ' . $paymentRequest->display_reference . ' for $' . number_format((float) $paymentRequest->amount, 2) . ' has been approved.';
            $endsAt = now()->addDays(30);
        } elseif ($paymentRequest->isCancelled()) {
            $iconClass = 'fa-solid fa-ban';
            $backgroundColor = '#f3f4f6';
            $title = 'Payment Cancelled ' . $paymentRequest->display_reference;
            $content = 'Payment request ' . $paymentRequest->display_reference . ' was cancelled.';

            if (filled($paymentRequest->cancellation_reason)) {
                $content .= ' Reason: ' . $paymentRequest->cancellation_reason;
            }

            $endsAt = now()->addDays(30);
        }

        $notice->fill([
            'title' => $title,
            'content' => $content,
            'icon_class' => $iconClass,
            'background_color' => $backgroundColor,
            'audience' => Notice::AUDIENCE_CLIENT,
            'created_by' => $paymentRequest->requested_by,
            'action_url' => route('client.dashboard'),
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => $endsAt,
        ]);

        $shouldResetDismissals = $notice->exists && $notice->isDirty([
            'title',
            'content',
            'icon_class',
            'background_color',
            'action_url',
            'is_active',
            'ends_at',
        ]);

        $notice->save();

        if ($shouldResetDismissals) {
            $notice->dismissals()->delete();
        }

        return $notice;
    }

    public function syncOnboardingNotice(User $client, ?User $creator = null): ?Notice
    {
        $profile = $client->clientProfile;

        if (!$profile instanceof ClientProfile) {
            return null;
        }

        $notice = Notice::firstOrNew([
            'source_type' => Notice::SOURCE_ONBOARDING_REQUEST,
            'source_id' => $profile->id,
            'recipient_user_id' => $client->id,
        ]);

        if (!$profile->shouldShowOnboardingForm()) {
            if ($notice->exists) {
                $notice->update([
                    'is_active' => false,
                    'ends_at' => now(),
                ]);
            }

            return $notice->exists ? $notice->fresh() : null;
        }

        $content = 'Submit your resume, signature, and onboarding details so we can continue your setup.';

        if ($profile->onboarding_requested_at) {
            $content .= ' Requested ' . $profile->onboarding_requested_at->diffForHumans() . '.';
        }

        if ($profile->resolvedOnboardingStatus() === ClientProfile::ONBOARDING_STATUS_REQUESTED_AGAIN) {
            $content = 'Admin requested updated onboarding details from you. Please review the onboarding form and submit it again.';
        }

        $notice->fill([
            'title' => 'We need your onboarding details.',
            'content' => $content,
            'icon_class' => 'fa-solid fa-file-lines',
            'background_color' => '#e9f9ff',
            'audience' => Notice::AUDIENCE_CLIENT,
            'created_by' => $creator?->id,
            'action_url' => route('client.onboarding.create'),
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => null,
        ]);

        $shouldResetDismissals = $notice->exists && $notice->isDirty([
            'title',
            'content',
            'icon_class',
            'background_color',
            'action_url',
            'is_active',
            'ends_at',
        ]);

        $notice->save();

        if ($shouldResetDismissals) {
            $notice->dismissals()->delete();
        }

        return $notice;
    }

    public function syncClientServiceNotice(User $client, ?AgentClientAssignment $assignment = null): ?Notice
    {
        $assignment ??= AgentClientAssignment::query()
            ->where('client_id', $client->id)
            ->where('is_active', true)
            ->latest('assigned_date')
            ->latest('id')
            ->first();

        $notice = Notice::firstOrNew([
            'source_type' => Notice::SOURCE_SERVICE_STATUS,
            'recipient_user_id' => $client->id,
        ]);

        if ($assignment?->isServiceCompleted()) {
            if ($notice->exists) {
                $notice->update([
                    'source_id' => $assignment->id,
                    'is_active' => false,
                    'ends_at' => now(),
                ]);
            }

            return $notice->exists ? $notice->fresh() : null;
        }

        if (!$assignment || !$assignment->service_end_date) {
            if ($notice->exists) {
                $notice->update([
                    'source_id' => $assignment?->id,
                    'is_active' => false,
                    'ends_at' => now(),
                ]);
            }

            return $notice->exists ? $notice->fresh() : null;
        }

        $daysRemaining = $assignment->getDaysRemaining();
        $shouldShowEndingSoon = $daysRemaining !== null && $daysRemaining >= 0 && $daysRemaining <= 3;
        $shouldShowExpired = $daysRemaining !== null && $daysRemaining < 0;

        if (!$shouldShowEndingSoon && !$shouldShowExpired) {
            if ($notice->exists) {
                $notice->update([
                    'source_id' => $assignment->id,
                    'is_active' => false,
                    'ends_at' => now(),
                ]);
            }

            return $notice->exists ? $notice->fresh() : null;
        }

        $serviceEndDate = $assignment->service_end_date->format('M j, Y');
        $title = 'Service Ending Soon';
        $content = 'Your service will expire in ' . $daysRemaining . ' day(s) on ' . $serviceEndDate . '. Please contact support to renew.';
        $iconClass = 'fa-solid fa-clock';
        $backgroundColor = '#fff7e0';

        if ($shouldShowExpired) {
            $title = 'Service Expired';
            $content = 'Your service ended ' . abs($daysRemaining) . ' day(s) ago on ' . $serviceEndDate . '. Please contact support to renew.';
            $iconClass = 'fa-solid fa-circle-exclamation';
            $backgroundColor = '#fff0f0';
        }

        $notice->fill([
            'title' => $title,
            'content' => $content,
            'icon_class' => $iconClass,
            'background_color' => $backgroundColor,
            'audience' => Notice::AUDIENCE_CLIENT,
            'recipient_user_id' => $client->id,
            'created_by' => $assignment->agent_id,
            'source_id' => $assignment->id,
            'action_url' => route('client.support-tickets.index'),
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => null,
        ]);

        $shouldResetDismissals = $notice->exists && $notice->isDirty([
            'title',
            'content',
            'icon_class',
            'background_color',
            'action_url',
            'is_active',
            'source_id',
        ]);

        $notice->save();

        if ($shouldResetDismissals) {
            $notice->dismissals()->delete();
        }

        return $notice;
    }

    protected function baseVisibleQuery(User $user)
    {
        return $this->baseVisibleQueryWithoutDismissals($user)
            ->notDismissedFor($user);
    }

    protected function baseVisibleQueryWithoutDismissals(User $user)
    {
        return Notice::query()
            ->active()
            ->visibleToUser($user);
    }
}
