<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\Notification;
use App\Models\PaymentRequest;
use App\Models\User;
use App\Services\EmailTemplateService;
use App\Services\NoticeService;
use App\Services\NotificationService;
use App\Services\UserCommunicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PaymentRequestController extends Controller
{
    public function __construct(
        protected EmailTemplateService $emailTemplateService,
        protected NotificationService $notificationService,
        protected NoticeService $noticeService,
        protected UserCommunicationService $communicationService,
    ) {
    }

    public function index(Request $request)
    {
        return view('admin.payment-requests.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role_id', User::ROLE_CLIENT)),
            ],
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:2000',
            'payment_link' => 'nullable|url|max:2048',
        ]);

        $paymentRequest = PaymentRequest::create([
            'client_id' => $data['client_id'],
            'requested_by' => Auth::id(),
            'amount' => $data['amount'],
            'note' => $data['note'] ?? null,
            'payment_link' => $data['payment_link'] ?? null,
            'status' => PaymentRequest::STATUS_PENDING,
        ]);

        // Email client
        $client = $paymentRequest->client;
        if ($client) {
            $this->sendPaymentRequestEmail($paymentRequest);

            $this->notificationService->notify(
                $client,
                'Payment Request ' . $paymentRequest->display_reference,
                'A payment request ' . $paymentRequest->display_reference . ' for $' . number_format((float) $paymentRequest->amount, 2) . ' has been sent to your account.',
                Notification::TYPE_WARNING,
                [
                    'payment_request_id' => $paymentRequest->id,
                    'payment_request_reference' => $paymentRequest->reference_number,
                    'amount' => $paymentRequest->amount,
                    'category' => 'payment_request',
                ],
                Notification::PRIORITY_HIGH,
                $paymentRequest,
                route('client.notices.index')
            );
        }

        $this->noticeService->syncPaymentRequestNotice($paymentRequest);

        return back()->with('success', 'Payment request ' . $paymentRequest->display_reference . ' sent to client.');
    }

    public function approve(PaymentRequest $paymentRequest)
    {
        if ($paymentRequest->isCancelled()) {
            return back()->with('info', 'Cancelled payment requests cannot be approved.');
        }

        if ($paymentRequest->status === PaymentRequest::STATUS_APPROVED) {
            return back()->with('info', 'This payment is already approved.');
        }

        if ($paymentRequest->status === PaymentRequest::STATUS_CLIENT_MARKED && !$paymentRequest->hasPaymentProof()) {
            return back()->with('info', 'A payment proof screenshot is required before approval.');
        }

        $paymentRequest->update([
            'status' => PaymentRequest::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            'cancelled_by' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);

        if ($paymentRequest->client) {
            $this->notificationService->notify(
                $paymentRequest->client,
                'Payment Approved ' . $paymentRequest->display_reference,
                'Your payment ' . $paymentRequest->display_reference . ' for $' . number_format((float) $paymentRequest->amount, 2) . ' has been approved.',
                Notification::TYPE_APPROVAL,
                [
                    'payment_request_id' => $paymentRequest->id,
                    'payment_request_reference' => $paymentRequest->reference_number,
                    'amount' => $paymentRequest->amount,
                    'category' => 'payment_request',
                ],
                Notification::PRIORITY_HIGH,
                $paymentRequest,
                route('client.notices.index')
            );

            $this->sendPaymentLifecycleEmail(
                $paymentRequest,
                'Payment Approved ' . $paymentRequest->display_reference,
                'Your payment has been approved.',
                [
                    'Amount: $' . number_format((float) $paymentRequest->amount, 2),
                    'Reference: ' . $paymentRequest->display_reference,
                ],
                route('client.dashboard'),
                'Open Dashboard'
            );
        }

        $this->noticeService->syncPaymentRequestNotice($paymentRequest);

        return back()->with('success', 'Payment ' . $paymentRequest->display_reference . ' marked as approved.');
    }

    public function reject(Request $request, PaymentRequest $paymentRequest)
    {
        if ($paymentRequest->isCancelled()) {
            return back()->with('info', 'Cancelled payment requests cannot be rejected.');
        }

        if ($paymentRequest->status === PaymentRequest::STATUS_APPROVED) {
            return back()->with('info', 'Approved payments cannot be rejected.');
        }

        if (!$paymentRequest->canBeRejected()) {
            return back()->with('info', 'Only payments marked as paid by the client can be rejected.');
        }

        $data = $request->validate([
            'rejection_reason' => 'required|string|max:2000',
        ]);

        $paymentRequest->update([
            'status' => PaymentRequest::STATUS_PENDING,
            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'rejection_reason' => $data['rejection_reason'],
            'cancelled_by' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);

        if ($paymentRequest->client) {
            $this->notificationService->notify(
                $paymentRequest->client,
                'Payment Rejected ' . $paymentRequest->display_reference,
                'Your payment ' . $paymentRequest->display_reference . ' for $' . number_format((float) $paymentRequest->amount, 2) . ' was rejected. Reason: ' . $data['rejection_reason'],
                Notification::TYPE_REJECTION,
                [
                    'payment_request_id' => $paymentRequest->id,
                    'payment_request_reference' => $paymentRequest->reference_number,
                    'amount' => $paymentRequest->amount,
                    'reason' => $data['rejection_reason'],
                    'category' => 'payment_request',
                ],
                Notification::PRIORITY_HIGH,
                $paymentRequest,
                route('client.notices.index')
            );

            $this->sendPaymentLifecycleEmail(
                $paymentRequest,
                'Payment Rejected ' . $paymentRequest->display_reference,
                'Your payment proof needs attention.',
                [
                    'Amount: $' . number_format((float) $paymentRequest->amount, 2),
                    'Reason: ' . $data['rejection_reason'],
                    'Please upload a fresh screenshot after correcting the issue.',
                ],
                route('client.dashboard'),
                'Open Dashboard'
            );
        }

        $this->noticeService->syncPaymentRequestNotice($paymentRequest);

        return back()->with('success', 'Payment ' . $paymentRequest->display_reference . ' rejected and client notified.');
    }

    public function cancel(Request $request, PaymentRequest $paymentRequest)
    {
        if ($paymentRequest->status === PaymentRequest::STATUS_APPROVED) {
            return back()->with('info', 'Approved payments cannot be cancelled.');
        }

        if ($paymentRequest->isCancelled()) {
            return back()->with('info', 'This payment request is already cancelled.');
        }

        $data = $request->validate([
            'cancellation_reason' => 'nullable|string|max:2000',
        ]);

        $paymentRequest->update([
            'status' => PaymentRequest::STATUS_PENDING,
            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            'client_marked_at' => null,
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now(),
            'cancellation_reason' => $data['cancellation_reason'] ?? null,
        ]);

        if ($paymentRequest->client) {
            $message = 'Payment request ' . $paymentRequest->display_reference . ' for $' . number_format((float) $paymentRequest->amount, 2) . ' has been cancelled.';

            if (filled($data['cancellation_reason'] ?? null)) {
                $message .= ' Reason: ' . $data['cancellation_reason'];
            }

            $this->notificationService->notify(
                $paymentRequest->client,
                'Payment Request Cancelled ' . $paymentRequest->display_reference,
                $message,
                Notification::TYPE_INFO,
                [
                    'payment_request_id' => $paymentRequest->id,
                    'payment_request_reference' => $paymentRequest->reference_number,
                    'amount' => $paymentRequest->amount,
                    'reason' => $data['cancellation_reason'] ?? null,
                    'category' => 'payment_request',
                ],
                Notification::PRIORITY_NORMAL,
                $paymentRequest,
                route('client.notices.index')
            );

            $emailLines = [
                'Amount: $' . number_format((float) $paymentRequest->amount, 2),
                'Reference: ' . $paymentRequest->display_reference,
            ];

            if (filled($data['cancellation_reason'] ?? null)) {
                $emailLines[] = 'Reason: ' . $data['cancellation_reason'];
            }

            $this->sendPaymentLifecycleEmail(
                $paymentRequest,
                'Payment Request Cancelled ' . $paymentRequest->display_reference,
                'An admin cancelled this payment request.',
                $emailLines,
                route('client.dashboard'),
                'Open Dashboard'
            );
        }

        $this->noticeService->syncPaymentRequestNotice($paymentRequest);

        return back()->with('success', 'Payment request ' . $paymentRequest->display_reference . ' cancelled.');
    }

    private function sendPaymentRequestEmail(PaymentRequest $paymentRequest): void
    {
        $client = $paymentRequest->client;

        if (!$client || blank($client->email)) {
            return;
        }

        $amount = number_format((float) $paymentRequest->amount, 2);
        $clientName = $client->name ?: 'Client';
        $statusLabel = $paymentRequest->getDisplayStatusLabel();
        $note = trim((string) ($paymentRequest->note ?? ''));
        $paymentLink = trim((string) ($paymentRequest->payment_link ?? ''));
        $loginUrl = route('login');
        $noteForTemplate = $note !== '' ? $note : 'No note provided';

        if ($paymentLink !== '') {
            $noteForTemplate .= "\nPayment link: {$paymentLink}";
        }

        $fallbackBody = view('emails.payment-request', [
            'paymentRequest' => $paymentRequest,
            'clientName' => $clientName,
            'amount' => $amount,
            'loginUrl' => $loginUrl,
            'paymentLink' => $paymentLink,
        ])->render();

        try {
            $this->emailTemplateService->sendTemplate(
                EmailTemplate::KEY_PAYMENT_REQUEST,
                (string) $client->email,
                (string) $clientName,
                [
                    'client_name' => $clientName,
                    'payment_reference' => $paymentRequest->display_reference,
                    'amount' => $amount,
                    'status' => $statusLabel,
                    'note' => $noteForTemplate,
                    'login_url' => $loginUrl,
                ],
                [
                    'subject_fallback' => "Payment Request {$paymentRequest->display_reference} - \${$amount}",
                    'body_fallback' => $fallbackBody,
                ]
            );
        } catch (\Throwable $exception) {
            Log::error('Failed to send payment request email.', [
                'payment_request_id' => $paymentRequest->id,
                'payment_request_reference' => $paymentRequest->reference_number,
                'client_id' => $client->id,
                'client_email' => $client->email,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    private function sendPaymentLifecycleEmail(
        PaymentRequest $paymentRequest,
        string $subject,
        string $headline,
        array $paragraphs,
        ?string $actionUrl = null,
        ?string $actionLabel = null
    ): void {
        $client = $paymentRequest->client;

        if (!$client) {
            return;
        }

        $this->communicationService->sendStructuredEmail(
            $client,
            $subject,
            $headline,
            $paragraphs,
            $actionUrl,
            $actionLabel
        );
    }
}
