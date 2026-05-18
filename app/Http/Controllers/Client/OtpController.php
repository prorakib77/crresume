<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\OtpSubmission;
use App\Models\OtpVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    /**
     * List OTP requests for the authenticated client.
     */
    public function index()
    {
        $otps = OtpVerification::with(['agent'])
            ->where('client_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(15);

        // Map latest submission status for each OTP verification
        $otpStatuses = \App\Models\OtpSubmission::whereIn('otp_verification_id', $otps->pluck('id'))
            ->latest('created_at')
            ->get()
            ->keyBy('otp_verification_id');

        return view('client.otp-requests', compact('otps', 'otpStatuses'));
    }

    public function submit(OtpVerification $otpVerification)
    {
        $otpVerification->load(['agent', 'client']);

        return view('client.otp-submit', compact('otpVerification'));
    }

    public function verify(Request $request, OtpVerification $otpVerification)
    {
        Log::info('OTP verification request received', [
            'otp_id' => $otpVerification->id,
            'otp_code' => $request->otp_code,
            'client_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        $request->validate([
            'company_name' => 'required|string|max:255',
            'otp_code' => 'required|string|max:255'
        ]);

        try {
            Log::info('OTP verification found', [
                'otp_id' => $otpVerification->id,
                'client_id' => $otpVerification->client_id,
                'is_verified' => $otpVerification->is_verified,
                'expires_at' => $otpVerification->expires_at
            ]);

            // Check if already verified
            if ($otpVerification->is_verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'This verification code has already been submitted.'
                ], 400);
            }

            // Check if expired
            if ($otpVerification->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This verification code has expired. Please request a new one.'
                ], 400);
            }

            $otpSubmission = DB::transaction(function () use ($request, $otpVerification) {
                $otpVerification->update([
                    'is_verified' => true,
                    'verified_at' => now(),
                    'message' => $otpVerification->message . "\n\nSubmitted Company: " . $request->company_name . "\nSubmitted OTP: " . $request->otp_code
                ]);

                $otpSubmission = OtpSubmission::create([
                    'otp_verification_id' => $otpVerification->id,
                    'agent_id' => $otpVerification->agent_id,
                    'client_id' => $otpVerification->client_id,
                    'company_name' => $request->company_name,
                    'otp_code' => $request->otp_code,
                    'status' => 'pending',
                    'submitted_at' => now()
                ]);

                Notification::create([
                    'user_id' => $otpVerification->agent_id,
                    'type' => 'otp_submission',
                    'title' => 'New OTP Submission Received',
                    'message' => "Client {$otpVerification->client->name} submitted OTP {$request->otp_code} for {$request->company_name}",
                    'data' => [
                        'otp_submission_id' => $otpSubmission->id,
                        'client_name' => $otpVerification->client->name,
                        'company_name' => $request->company_name,
                        'otp_code' => $request->otp_code
                    ]
                ]);

                return $otpSubmission;
            });

            try {
                Mail::to($otpVerification->agent->email)->send(new \App\Mail\OtpSubmissionNotificationMail($otpSubmission));
            } catch (\Throwable $mailException) {
                Log::warning('OTP submission email notification failed', [
                    'otp_id' => $otpVerification->id,
                    'otp_submission_id' => $otpSubmission->id,
                    'agent_id' => $otpVerification->agent_id,
                    'error' => $mailException->getMessage(),
                ]);
            }

            // Log the successful verification
            Log::info('Company & OTP submission successful', [
                'otp_id' => $otpVerification->id,
                'otp_submission_id' => $otpSubmission->id,
                'agent_id' => $otpVerification->agent_id,
                'client_id' => $otpVerification->client_id,
                'company_name' => $request->company_name,
                'otp_code' => $request->otp_code,
                'verified_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Company information and OTP submitted successfully! Thank you for your response.'
            ]);

        } catch (\Exception $e) {
            Log::error('OTP verification failed: ' . $e->getMessage(), [
                'otp_id' => $otpVerification->id ?? null,
                'client_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while verifying the code. Please try again.',
                'debug' => $e->getMessage()
            ], 500);
        }
    }
}
