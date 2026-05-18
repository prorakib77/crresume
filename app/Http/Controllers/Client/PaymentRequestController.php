<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use App\Services\NoticeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PaymentRequestController extends Controller
{
    public function __construct(
        protected NoticeService $noticeService
    ) {
    }

    /**
     * Client marks a payment request as paid (pending admin approval).
     */
    public function markPaid(Request $request, PaymentRequest $paymentRequest)
    {
        $user = Auth::user();

        if ((int) $paymentRequest->client_id !== (int) $user->id) {
            abort(403);
        }

        if ($paymentRequest->status === PaymentRequest::STATUS_APPROVED) {
            return back()->with('info', 'This payment was already approved.');
        }

        if ($paymentRequest->isCancelled()) {
            return back()->with('info', 'This payment request has been cancelled by the admin.');
        }

        $data = $request->validate([
            'payment_request_id' => 'nullable|integer',
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($paymentRequest->hasPaymentProof()) {
            Storage::disk('public')->delete($paymentRequest->payment_proof_path);
        }

        $paymentProofPath = $request->file('payment_proof')->store('payment-proofs/' . $user->id, 'public');

        $paymentRequest->update([
            'status' => PaymentRequest::STATUS_CLIENT_MARKED,
            'client_marked_at' => now(),
            'payment_proof_path' => $paymentProofPath,
            'payment_proof_uploaded_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            'cancelled_by' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);

        $this->noticeService->syncPaymentRequestNotice($paymentRequest);

        return back()->with('success', 'Thanks! We marked ' . $paymentRequest->display_reference . ' as paid and attached your payment proof for admin review.');
    }
}
