<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AgentClientAssignment;
use App\Models\ClientSalesPopup;
use App\Models\ClientProfile;
use App\Models\PaymentRequest;
use App\Models\WorkUpdate;
use App\Services\NoticeService;
use App\Support\WorkUpdateFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Throwable;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardController extends Controller
{
    public function __construct(
        protected NoticeService $noticeService
    ) {
    }

    public function index()
    {
        $user = Auth::user();

        // Ensure client profile exists and onboarding flag defaults to visible for new clients
        $profile = $user->clientProfile;
        if (!$profile) {
            $profile = ClientProfile::create([
                'user_id' => $user->id,
                'status' => 0,
                'onboarding_visible' => true,
                'onboarding_status' => ClientProfile::ONBOARDING_STATUS_PENDING,
                'service_type' => ClientProfile::SERVICE_TYPE_REGULAR,
            ]);
        } elseif ($profile->onboarding_visible === null) {
            $profile->update([
                'onboarding_visible' => true,
                'onboarding_status' => $profile->resolvedOnboardingStatus(),
            ]);
        }

        if ($profile->shouldShowOnboardingForm()) {
            $this->noticeService->syncOnboardingNotice($user);
        }

        // Get client's active assignment
        $assignment = AgentClientAssignment::where('client_id', $user->id)
            ->where('is_active', true)
            ->latest('assigned_date')
            ->latest('id')
            ->with('agent')
            ->first();

        $this->noticeService->syncClientServiceNotice($user, $assignment);

        $stats = [
            'total_updates' => WorkUpdate::where('client_id', $user->id)
                                        ->whereIn('status', [WorkUpdate::STATUS_SUBMITTED, WorkUpdate::STATUS_APPROVED])
                                        ->count(),
            'this_month' => WorkUpdate::where('client_id', $user->id)
                                    ->whereIn('status', [WorkUpdate::STATUS_SUBMITTED, WorkUpdate::STATUS_APPROVED])
                                    ->whereMonth('created_at', now()->month)
                                    ->count(),
            'last_update' => WorkUpdate::where('client_id', $user->id)
                                     ->whereIn('status', [WorkUpdate::STATUS_SUBMITTED, WorkUpdate::STATUS_APPROVED])
                                     ->latest('created_at')
                                     ->first()
        ];

        $recentWorkUpdates = WorkUpdate::where('client_id', $user->id)
            ->whereIn('status', [WorkUpdate::STATUS_SUBMITTED, WorkUpdate::STATUS_APPROVED])
            ->with('agent')
            ->latest('applied_date')
            ->latest('created_at')
            ->limit(4)
            ->get();

        $paymentRequests = PaymentRequest::where('client_id', $user->id)
            ->whereIn('status', [PaymentRequest::STATUS_PENDING, PaymentRequest::STATUS_CLIENT_MARKED])
            ->whereNull('cancelled_at')
            ->orderByDesc('created_at')
            ->get();

        $dashboardNotices = $this->noticeService->getDashboardNotices($user);

        $isRecurringClient = AgentClientAssignment::where('client_id', $user->id)->count() > 1;
        $clientSalesPopup = null;

        if (Schema::hasTable('client_sales_popups')) {
            $specificPopup = ClientSalesPopup::query()
                ->active()
                ->live()
                ->where('target_type', ClientSalesPopup::TARGET_SPECIFIC)
                ->where('target_client_id', $user->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->first();

            if ($specificPopup) {
                $clientSalesPopup = $specificPopup;
            } elseif ($isRecurringClient) {
                $clientSalesPopup = ClientSalesPopup::query()
                    ->active()
                    ->live()
                    ->where('target_type', ClientSalesPopup::TARGET_RECURRING)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->first();
            }
        }

        return view('client.dashboard', compact(
            'stats',
            'assignment',
            'profile',
            'paymentRequests',
            'dashboardNotices',
            'clientSalesPopup',
            'recentWorkUpdates'
        ));
    }

    /**
     * Display the dedicated work updates page for clients.
     */
    public function workUpdates(Request $request)
    {
        return view('client.work-updates');
    }

    public function editWorkUpdate(string $workUpdate)
    {
        $workUpdate = $this->resolveClientWorkUpdateFromRouteKey($workUpdate);

        if (!ctype_digit($this->normalizeRouteKeyValue($workUpdate->id, request()->route('workUpdate')))) {
            return redirect()->route('client.work-updates.edit', $workUpdate->id);
        }

        return view('client.work-updates-edit', [
            'workUpdate' => $workUpdate,
            'editableStatuses' => WorkUpdate::getClientEditableApplicationStatuses(),
        ]);
    }

    public function updateWorkUpdate(Request $request, string $workUpdate)
    {
        $workUpdate = $this->resolveClientWorkUpdateFromRouteKey($workUpdate);
        $editableStatuses = WorkUpdate::getClientEditableApplicationStatuses();

        $validated = $request->validate([
            'application_status' => ['required', 'string', Rule::in(array_keys($editableStatuses))],
        ], [
            'application_status.required' => 'Please select an application status.',
            'application_status.in' => 'Please select a valid client update status.',
        ]);

        try {
            $updatedRows = DB::table('work_updates')
                ->where('id', $workUpdate->id)
                ->where('client_id', Auth::id())
                ->whereIn('status', [WorkUpdate::STATUS_SUBMITTED, WorkUpdate::STATUS_APPROVED])
                ->update([
                    'application_status' => $validated['application_status'],
                    'updated_at' => now(),
                ]);
        } catch (Throwable $exception) {
            Log::error('Client work update status update failed.', [
                'work_update_id' => $workUpdate->id,
                'client_id' => Auth::id(),
                'requested_status' => $validated['application_status'],
                'error' => $exception->getMessage(),
            ]);

            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Unable to update the work status right now. Please try again.');
        }

        if ($updatedRows === 0) {
            return back()
                ->withInput()
                ->with('error', 'This work update is no longer available for client status changes.');
        }

        $workUpdate->refresh();

        return redirect()
            ->route('client.work-updates.edit', $workUpdate->id)
            ->with('success', 'Work update status updated successfully.');
    }

    public function downloadPdf(Request $request)
    {
        $user = Auth::user();

        $workUpdates = WorkUpdateFilters::client($user, $request->only([
                'search',
                'date_from',
                'date_to',
                'application_status',
            ]))
            ->latest('applied_date')
            ->latest('created_at')
            ->get();

        $pdf = Pdf::loadView('client.work-updates-pdf', compact('workUpdates', 'user'));

        return $pdf->download('work-updates-' . now()->format('Y-m-d') . '.pdf');
    }

    public function downloadCsv(Request $request)
    {
        $user = Auth::user();

        $workUpdates = WorkUpdateFilters::client($user, $request->only([
                'search',
                'date_from',
                'date_to',
                'application_status',
            ]))
            ->latest('applied_date')
            ->latest('created_at')
            ->get();

        $filename = 'work-updates-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($workUpdates) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'Date',
                'Job Title',
                'Company',
                'Applied Method',
                'Application Status',
                'Job Link',
                'Success Link',
                'Note'
            ]);

            // Add data rows
            foreach ($workUpdates as $update) {
                fputcsv($file, [
                    ($update->applied_date ?? $update->created_at)?->format('Y-m-d'),
                    $update->job_title,
                    $update->company,
                    $update->getAppliedMethodLabel(),
                    $update->getApplicationStatusLabel(),
                    $update->job_link,
                    $update->job_success_link,
                    $update->note
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function filterUpdates(Request $request)
    {
        return redirect()->route('client.work-updates.index', $request->only([
            'search',
            'date_from',
            'date_to',
            'application_status',
        ]));
    }

    private function resolveClientWorkUpdate(WorkUpdate $workUpdate): WorkUpdate
    {
        $user = Auth::user();

        if ((int) $workUpdate->client_id !== (int) $user->id) {
            Log::warning('Client attempted to access a work update that does not belong to them.', [
                'work_update_id' => $workUpdate->id,
                'route_key' => request()->route('workUpdate'),
                'expected_client_id' => $workUpdate->client_id,
                'authenticated_client_id' => $user->id,
            ]);

            abort(404);
        }

        if (!in_array($workUpdate->status, [WorkUpdate::STATUS_SUBMITTED, WorkUpdate::STATUS_APPROVED], true)) {
            Log::warning('Client attempted to access a work update with an unavailable status.', [
                'work_update_id' => $workUpdate->id,
                'route_key' => request()->route('workUpdate'),
                'status' => $workUpdate->status,
                'client_id' => $user->id,
            ]);

            abort(404);
        }

        $workUpdate->loadMissing('agent');

        return $workUpdate;
    }

    private function resolveClientWorkUpdateFromRouteKey(string $routeKey): WorkUpdate
    {
        $resolvedWorkUpdate = (new WorkUpdate())->resolveRouteBinding($routeKey);

        if (!$resolvedWorkUpdate instanceof WorkUpdate) {
            Log::warning('Client work update route key could not be resolved.', [
                'route_key' => $routeKey,
                'client_id' => Auth::id(),
            ]);

            abort(404);
        }

        return $this->resolveClientWorkUpdate($resolvedWorkUpdate);
    }

    private function normalizeRouteKeyValue(int $workUpdateId, mixed $originalRouteKey): string
    {
        $original = trim((string) $originalRouteKey);

        if ($original === '') {
            return (string) $workUpdateId;
        }

        return $original;
    }

}
