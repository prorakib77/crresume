<?php

use App\Models\PaymentRequest;
use App\Models\User;
use App\Services\NoticeService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        /** @var NoticeService $noticeService */
        $noticeService = app(NoticeService::class);

        PaymentRequest::query()
            ->with('client')
            ->orderBy('id')
            ->get()
            ->each(function (PaymentRequest $paymentRequest) use ($noticeService) {
                $noticeService->syncPaymentRequestNotice($paymentRequest);
            });

        User::query()
            ->where('role_id', User::ROLE_CLIENT)
            ->with('clientProfile')
            ->get()
            ->each(function (User $client) use ($noticeService) {
                if ($client->clientProfile?->onboarding_visible) {
                    $noticeService->syncOnboardingNotice($client);
                }
            });
    }

    public function down(): void
    {
        // Backfilled system notices are kept intentionally.
    }
};
