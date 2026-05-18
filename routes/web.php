<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ClientSalesPopupController as AdminClientSalesPopupController;
use App\Http\Controllers\Admin\EmailTemplateController as AdminEmailTemplateController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\NoticeController as AdminNoticeController;
use App\Http\Controllers\Admin\PaymentRequestController as AdminPaymentRequestController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SaleCountdownController as AdminSaleCountdownController;
use App\Http\Controllers\Agent\DashboardController as AgentDashboardController;
use App\Http\Controllers\Agent\NoticeController as AgentNoticeController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\NoticeController as ClientNoticeController;
use App\Http\Controllers\Client\PaymentRequestController as ClientPaymentRequestController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupportTicketController;
use App\Models\Product;
use App\Models\Review;
use App\Models\SaleCountdown;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    $settings = \App\Models\CustomizationSetting::getAllActive();
    $siteName = $settings->get('site_name')?->setting_value ?? config('app.name', 'W-Automation');
    $products = collect();
    $reviews = collect();
    $saleCountdown = null;

    if (Schema::hasTable('products')) {
        $products = Product::query()
            ->active()
            ->forType(Product::TYPE_FULL_SERVICE)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    if (Schema::hasTable('reviews')) {
        $reviews = Review::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    if (Schema::hasTable('sale_countdowns')) {
        $saleCountdown = SaleCountdown::query()
            ->activeAndLive()
            ->orderBy('sort_order')
            ->orderBy('end_at')
            ->orderBy('id')
            ->first();
    }

    return view('welcome', compact('settings', 'siteName', 'products', 'reviews', 'saleCountdown'));
});

Route::get('/reviews', [PublicPageController::class, 'reviews'])->name('reviews.page');
Route::get('/faqs', [PublicPageController::class, 'faqs'])->name('faqs.page');
Route::get('/guide', [PublicPageController::class, 'clientGuide'])->name('guide.page');
Route::get('/privacy-policy', [PublicPageController::class, 'privacyPolicy'])->name('privacy-policy.page');
Route::get('/terms-of-service', [PublicPageController::class, 'termsOfService'])->name('terms-of-service.page');
Route::get('/booking-policy', [PublicPageController::class, 'bookingPolicy'])->name('booking-policy.page');
Route::get('/refund-policy', [PublicPageController::class, 'refundPolicy'])->name('refund-policy.page');
Route::get('/contact', [PublicPageController::class, 'contact'])->name('contact.page');
Route::post('/contact', [PublicPageController::class, 'submitContact'])->name('contact.submit');

Route::middleware('auth')->get('/suspended', function () {
    $user = Auth::user();

    if (!$user || $user->status !== User::STATUS_SUSPENDED) {
        return redirect()->route('dashboard');
    }

    return view('auth.suspended', compact('user'));
})->name('account.suspended');

// Test route to check if routes are working
Route::get('/test', function () {
    return 'Routes are working!';
});

// Test admin routes without middleware
Route::get('/admin/test', function () {
    return 'Admin test route working!';
});

// Test agent routes without middleware
Route::get('/agent/test', function () {
    return 'Agent test route working!';
});

// Test error handling routes
Route::get('/test-error', function () {
    throw new \Exception('This is a test error to demonstrate error handling');
});

Route::get('/test-404', function () {
    abort(404, 'This page does not exist');
});

Route::get('/test-500', function () {
    abort(500, 'This is a server error');
});

// Dynamic dashboard route based on user role
Route::get('/dashboard', function () {
    $user = Auth::user();

    if (!$user || !$user->role_id) {
        return redirect()->route('login')->with('error', 'Your account does not have a valid role assigned. Please contact an administrator.');
    }

    switch ((int) $user->role_id) {
        case User::ROLE_SUPER_ADMIN:
        case User::ROLE_ADMIN:
            return redirect()->route('admin.dashboard');
        case User::ROLE_AGENT:
            return redirect()->route('agent.dashboard');
        case User::ROLE_CLIENT:
            return redirect()->route('client.dashboard');
        default:
            return redirect()->route('login')->with('error', 'Your account has an invalid role. Please contact an administrator.');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

// Debug route for testing agent dashboard
Route::get('/debug-agent', function () {
    $agent = \App\Models\User::where('role_id', 3)->first();
    if (!$agent) {
        return 'No agent found';
    }

    $assignedClients = $agent->getActiveClientsAttribute();
    $clientsStatus = [];

    foreach ($assignedClients as $client) {
        $todaySubmission = \App\Models\WorkUpdate::getTodaysSubmission($agent->id, $client->id);
        $clientsStatus[] = [
            'client' => $client,
            'has_submitted_today' => $todaySubmission !== null,
            'submission' => $todaySubmission,
            'service_end_date' => $client->pivot->service_end_date ?? null,
            'days_remaining' => $client->pivot->service_end_date ?
                now()->diffInDays($client->pivot->service_end_date, false) : null
        ];
    }

    return response()->json([
        'agent' => $agent->name,
        'assigned_clients_count' => $assignedClients->count(),
        'clients_status_count' => count($clientsStatus),
        'clients_status' => $clientsStatus
    ]);
})->middleware('auth');

// Admin Pass Key Routes (accessible to all authenticated users)
Route::middleware(['auth', 'canonical.route'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/passkey', [\App\Http\Controllers\AdminPassKeyController::class, 'showForm'])->name('passkey.form');
    Route::post('/passkey/verify', [\App\Http\Controllers\AdminPassKeyController::class, 'verify'])->name('passkey.verify');
    Route::post('/passkey/revoke', [\App\Http\Controllers\AdminPassKeyController::class, 'revoke'])->name('passkey.revoke');

    // Admin-only pass key management routes
    Route::middleware(['role:admin,super-admin'])->group(function () {
        Route::get('/passkey/change', [\App\Http\Controllers\AdminPassKeyController::class, 'showChangeForm'])->name('passkey.change');
        Route::post('/passkey/update', [\App\Http\Controllers\AdminPassKeyController::class, 'update'])->name('passkey.update');

        // Admin login as user routes
        Route::get('/login-as-user', [\App\Http\Controllers\AdminUserLoginController::class, 'showForm'])->name('login-as-user');
        Route::post('/login-as-user', [\App\Http\Controllers\AdminUserLoginController::class, 'loginAsUser'])->name('login-as-user.post');
        Route::get('/user-search', [\App\Http\Controllers\AdminUserLoginController::class, 'searchUsers'])->name('user-search');
    });
});

// Admin Routes (with pass key access for all users)
Route::middleware(['auth', 'admin.passkey', 'canonical.route'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/generate-meeting', [AdminDashboardController::class, 'showGenerateMeeting'])->name('generate-meeting');
    Route::post('/generate-meeting', [AdminDashboardController::class, 'generateMeeting'])->name('generate-meeting.post');
    Route::get('/meeting-report', [AdminDashboardController::class, 'getMeetingReport'])->name('meeting-report');
    Route::get('/meeting-export', [AdminDashboardController::class, 'exportMeetingReport'])->name('meeting-export');
    Route::get('/meeting-export/{meeting}', [AdminDashboardController::class, 'exportMeetingReportForMeeting'])->name('export-meeting-report');
    Route::get('/meeting-reports', [AdminDashboardController::class, 'meetingReports'])->name('meeting-reports');
    Route::get('/meeting-details/{meeting}', [AdminDashboardController::class, 'meetingDetails'])->name('meeting-details');
    Route::get('/meeting-test', [AdminDashboardController::class, 'meetingTest'])->name('meeting-test');
    Route::get('/meeting-dashboard', [AdminDashboardController::class, 'meetingDashboard'])->name('meeting-dashboard');
    Route::get('/meeting-setup', [AdminDashboardController::class, 'meetingSetup'])->name('meeting-setup');
    Route::post('/meeting-setup', [AdminDashboardController::class, 'storeMeetingSetup'])->name('meeting-setup.store');
    Route::delete('/meeting-setup/{meeting}', [AdminDashboardController::class, 'destroyMeetingSetup'])->name('meeting-setup.destroy');
    Route::post('/track-agent-join', [AdminDashboardController::class, 'trackAgentJoin'])->name('track-agent-join');
    Route::post('/track-screen-share', [AdminDashboardController::class, 'trackScreenShare'])->name('track-screen-share');

    // OAuth Management Routes
    Route::get('/oauth', [\App\Http\Controllers\Admin\OAuthController::class, 'index'])->name('oauth.index');
    Route::get('/oauth/create', [\App\Http\Controllers\Admin\OAuthController::class, 'create'])->name('oauth.create');
    Route::get('/oauth/setup-guide', function() { return view('admin.oauth.setup-guide'); })->name('oauth.setup-guide');
    Route::post('/oauth', [\App\Http\Controllers\Admin\OAuthController::class, 'store'])->name('oauth.store');
    Route::post('/oauth/test-connection', [\App\Http\Controllers\Admin\OAuthController::class, 'testConnection'])->name('oauth.test-connection');
    Route::post('/oauth/test-meeting', [\App\Http\Controllers\Admin\OAuthController::class, 'generateTestMeeting'])->name('oauth.test-meeting');
    Route::post('/oauth/reset', [\App\Http\Controllers\Admin\OAuthController::class, 'reset'])->name('oauth.reset');
    Route::get('/oauth/status', [\App\Http\Controllers\Admin\OAuthController::class, 'status'])->name('oauth.status');

    // Mailchimp Management Routes
    Route::get('/mailchimp', [\App\Http\Controllers\Admin\MailchimpSettingsController::class, 'index'])->name('mailchimp.index');
    Route::get('/mailchimp/create', [\App\Http\Controllers\Admin\MailchimpSettingsController::class, 'create'])->name('mailchimp.create');
    Route::post('/mailchimp', [\App\Http\Controllers\Admin\MailchimpSettingsController::class, 'store'])->name('mailchimp.store');
    Route::post('/mailchimp/test-connection', [\App\Http\Controllers\Admin\MailchimpSettingsController::class, 'testConnection'])->name('mailchimp.test-connection');
    Route::post('/mailchimp/get-lists', [\App\Http\Controllers\Admin\MailchimpSettingsController::class, 'getLists'])->name('mailchimp.get-lists');
    Route::post('/mailchimp/send-test-email', [\App\Http\Controllers\Admin\MailchimpSettingsController::class, 'sendTestEmail'])->name('mailchimp.send-test-email');
    Route::post('/mailchimp/reset', [\App\Http\Controllers\Admin\MailchimpSettingsController::class, 'reset'])->name('mailchimp.reset');
    Route::get('/mailchimp/status', [\App\Http\Controllers\Admin\MailchimpSettingsController::class, 'status'])->name('mailchimp.status');

    Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
    Route::get('/custom-email', [AdminDashboardController::class, 'userEmail'])->name('user-email.index');
    Route::get('/custom-email/search-users', [AdminDashboardController::class, 'searchEmailUsers'])->name('user-email.search-users');
    Route::get('/users/create', [AdminDashboardController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminDashboardController::class, 'storeUser'])->name('users.store');
    Route::post('/custom-email', [AdminDashboardController::class, 'sendUserEmail'])->name('user-email.send');
    Route::get('/users/{user}/edit', [AdminDashboardController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminDashboardController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminDashboardController::class, 'deleteUser'])->name('users.delete');

    Route::get('/assignments', [AdminDashboardController::class, 'assignments'])->name('assignments');
    Route::get('/assignments/create', [AdminDashboardController::class, 'createAssignment'])->name('assignments.create');
    Route::post('/assignments', [AdminDashboardController::class, 'storeAssignment'])->name('assignments.store');
    Route::get('/assignments/{assignment}/edit', [AdminDashboardController::class, 'editAssignment'])->name('assignments.edit');
    Route::put('/assignments/{assignment}', [AdminDashboardController::class, 'updateAssignment'])->name('assignments.update');
    Route::delete('/assignments/{assignment}', [AdminDashboardController::class, 'destroyAssignment'])->name('assignments.destroy');

    // AJAX search routes for assignments
    Route::get('/assignments/search-agents', [AdminDashboardController::class, 'searchAgents'])->name('assignments.search-agents');
    Route::get('/assignments/search-clients', [AdminDashboardController::class, 'searchClients'])->name('assignments.search-clients');

    // Product Cards
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

    // Review Cards
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::get('/reviews/create', [AdminReviewController::class, 'create'])->name('reviews.create');
    Route::post('/reviews', [AdminReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [AdminReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [AdminReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    // FAQ Items
    Route::get('/faqs', [AdminFaqController::class, 'index'])->name('faqs.index');
    Route::get('/faqs/create', [AdminFaqController::class, 'create'])->name('faqs.create');
    Route::post('/faqs', [AdminFaqController::class, 'store'])->name('faqs.store');
    Route::get('/faqs/{faq}/edit', [AdminFaqController::class, 'edit'])->name('faqs.edit');
    Route::put('/faqs/{faq}', [AdminFaqController::class, 'update'])->name('faqs.update');
    Route::delete('/faqs/{faq}', [AdminFaqController::class, 'destroy'])->name('faqs.destroy');

    // Countdown Sales
    Route::get('/sale-countdowns', [AdminSaleCountdownController::class, 'index'])->name('sale-countdowns.index');
    Route::get('/sale-countdowns/create', [AdminSaleCountdownController::class, 'create'])->name('sale-countdowns.create');
    Route::post('/sale-countdowns', [AdminSaleCountdownController::class, 'store'])->name('sale-countdowns.store');
    Route::get('/sale-countdowns/{saleCountdown}/edit', [AdminSaleCountdownController::class, 'edit'])->name('sale-countdowns.edit');
    Route::put('/sale-countdowns/{saleCountdown}', [AdminSaleCountdownController::class, 'update'])->name('sale-countdowns.update');
    Route::delete('/sale-countdowns/{saleCountdown}', [AdminSaleCountdownController::class, 'destroy'])->name('sale-countdowns.destroy');

    // Client Dashboard Sales Popups
    Route::get('/client-sales-popups', [AdminClientSalesPopupController::class, 'index'])->name('client-sales-popups.index');
    Route::get('/client-sales-popups/create', [AdminClientSalesPopupController::class, 'create'])->name('client-sales-popups.create');
    Route::post('/client-sales-popups', [AdminClientSalesPopupController::class, 'store'])->name('client-sales-popups.store');
    Route::get('/client-sales-popups/{clientSalesPopup}/edit', [AdminClientSalesPopupController::class, 'edit'])->name('client-sales-popups.edit');
    Route::put('/client-sales-popups/{clientSalesPopup}', [AdminClientSalesPopupController::class, 'update'])->name('client-sales-popups.update');
    Route::delete('/client-sales-popups/{clientSalesPopup}', [AdminClientSalesPopupController::class, 'destroy'])->name('client-sales-popups.destroy');

    Route::get('/work-updates', [AdminDashboardController::class, 'workUpdates'])->name('work-updates');
    Route::get('/work-updates/download/pdf', [AdminDashboardController::class, 'downloadWorkUpdatesPdf'])->name('work-updates.download.pdf');
    Route::get('/work-updates/download/csv', [AdminDashboardController::class, 'downloadWorkUpdatesCsv'])->name('work-updates.download.csv');

    Route::get('/impersonate/{user}', [AdminDashboardController::class, 'impersonate'])->name('impersonate');
    Route::get('/stop-impersonating', [AdminDashboardController::class, 'stopImpersonating'])->name('stop-impersonating');

    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/test-mailchimp', [\App\Http\Controllers\Admin\SettingsController::class, 'testMailchimp'])->name('settings.test-mailchimp');
    Route::post('/settings/clear-cache', [\App\Http\Controllers\Admin\SettingsController::class, 'clearCache'])->name('settings.clear-cache');
    Route::post('/settings/optimize', [\App\Http\Controllers\Admin\SettingsController::class, 'optimize'])->name('settings.optimize');

    // Email Templates
    Route::get('/email-templates', [AdminEmailTemplateController::class, 'index'])->name('email-templates.index');
    Route::post('/email-templates/send-all-tests', [AdminEmailTemplateController::class, 'sendAllTests'])->name('email-templates.send-all-tests');
    Route::get('/email-templates/{emailTemplate}/edit', [AdminEmailTemplateController::class, 'edit'])->name('email-templates.edit');
    Route::put('/email-templates/{emailTemplate}', [AdminEmailTemplateController::class, 'update'])->name('email-templates.update');
    Route::post('/email-templates/{emailTemplate}/reset', [AdminEmailTemplateController::class, 'reset'])->name('email-templates.reset');

    // PDF Templates
    Route::get('/pdf-templates', [\App\Http\Controllers\Admin\PdfTemplateController::class, 'index'])->name('pdf-templates.index');
    Route::get('/pdf-templates/{template}/edit', [\App\Http\Controllers\Admin\PdfTemplateController::class, 'edit'])->name('pdf-templates.edit');
    Route::put('/pdf-templates/{template}', [\App\Http\Controllers\Admin\PdfTemplateController::class, 'update'])->name('pdf-templates.update');
    Route::post('/pdf-templates/{template}/reset', [\App\Http\Controllers\Admin\PdfTemplateController::class, 'reset'])->name('pdf-templates.reset');

    // Customization routes
    Route::get('/customization', [\App\Http\Controllers\Admin\CustomizationController::class, 'index'])->name('customization');
    Route::get('/customization/section/{section}', [\App\Http\Controllers\Admin\CustomizationController::class, 'section'])->name('customization.section');
    Route::put('/customization/section/{section}', [\App\Http\Controllers\Admin\CustomizationController::class, 'updateSection'])->name('customization.section.update');
    Route::put('/customization', [\App\Http\Controllers\Admin\CustomizationController::class, 'update'])->name('customization.update');
    Route::post('/customization/reset', [\App\Http\Controllers\Admin\CustomizationController::class, 'reset'])->name('customization.reset');
    Route::post('/customization/upload', [\App\Http\Controllers\Admin\CustomizationController::class, 'uploadFile'])->name('customization.upload');
    Route::get('/customization/css', [\App\Http\Controllers\Admin\CustomizationController::class, 'getCssVariables'])->name('customization.css');
    Route::get('/notices', [AdminNoticeController::class, 'index'])->name('notices.index');
    Route::post('/notices', [AdminNoticeController::class, 'store'])->name('notices.store');
    Route::post('/notices/{notice}/toggle', [AdminNoticeController::class, 'toggle'])->name('notices.toggle');

    // Agent Management
    Route::get('/agents', [\App\Http\Controllers\Admin\AgentController::class, 'index'])->name('agents.index');
    Route::get('/agents/daily-report', [\App\Http\Controllers\Admin\AgentController::class, 'dailyReport'])->name('agents.daily-report');
    Route::get('/agents/{agent}', [\App\Http\Controllers\Admin\AgentController::class, 'show'])->name('agents.show');
    Route::get('/agents/{agent}/activity-data', [\App\Http\Controllers\Admin\AgentController::class, 'getActivityData'])->name('agents.activity-data');

    // Client Management
    Route::get('/clients', [\App\Http\Controllers\Admin\ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/{client}', [\App\Http\Controllers\Admin\ClientController::class, 'show'])->name('clients.show');
    Route::put('/clients/{client}/details', [\App\Http\Controllers\Admin\ClientController::class, 'updateDetails'])->name('clients.update-details');
    Route::post('/clients/{client}/send-email', [\App\Http\Controllers\Admin\ClientController::class, 'sendCustomEmail'])->name('clients.send-email');
    Route::post('/clients/{client}/request-onboarding', [\App\Http\Controllers\Admin\ClientController::class, 'requestOnboarding'])->name('clients.request-onboarding');
    Route::get('/clients/{client}/onboarding-text', [\App\Http\Controllers\Admin\ClientController::class, 'downloadOnboardingText'])->name('clients.onboarding-text');
    Route::get('/clients/{client}/onboarding-file/{type}', [\App\Http\Controllers\Admin\ClientController::class, 'downloadOnboardingFile'])->name('clients.onboarding-file');

    // Payment Requests
    Route::get('/payment-requests', [AdminPaymentRequestController::class, 'index'])->name('payment-requests.index');
    Route::post('/payment-requests', [AdminPaymentRequestController::class, 'store'])->name('payment-requests.store');
    Route::post('/payment-requests/{paymentRequest}/approve', [AdminPaymentRequestController::class, 'approve'])->name('payment-requests.approve');
    Route::post('/payment-requests/{paymentRequest}/reject', [AdminPaymentRequestController::class, 'reject'])->name('payment-requests.reject');
    Route::post('/payment-requests/{paymentRequest}/cancel', [AdminPaymentRequestController::class, 'cancel'])->name('payment-requests.cancel');

    // Support Tickets
    Route::get('/support-tickets', [SupportTicketController::class, 'index'])->name('support-tickets.index');
    Route::get('/support-tickets/create', [SupportTicketController::class, 'create'])->name('support-tickets.create');
    Route::post('/support-tickets', [SupportTicketController::class, 'store'])->name('support-tickets.store');
    Route::get('/support-tickets/{supportTicket}', [SupportTicketController::class, 'show'])->name('support-tickets.show');
    Route::get('/support-tickets/{supportTicket}/messages', [SupportTicketController::class, 'messages'])->name('support-tickets.messages');
    Route::post('/support-tickets/{supportTicket}/message', [SupportTicketController::class, 'storeMessage'])->name('support-tickets.message');
    Route::post('/support-tickets/{supportTicket}/assign-agent', [SupportTicketController::class, 'assignAgent'])->name('support-tickets.assign-agent');
    Route::post('/support-tickets/{supportTicket}/close', [SupportTicketController::class, 'close'])->name('support-tickets.close');
});

// Agent Routes
Route::middleware(['auth', 'role:agent', 'track.agent.activity', 'canonical.route'])->prefix('agent')->name('agent.')->group(function () {
    Route::get('/dashboard', [AgentDashboardController::class, 'index'])->name('dashboard');
    Route::get('/work-updates/create', [AgentDashboardController::class, 'createWorkUpdate'])->name('work-updates.create');
    Route::post('/work-updates', [AgentDashboardController::class, 'storeWorkUpdate'])->name('work-updates.store');
    Route::get('/work-updates', [AgentDashboardController::class, 'myWorkUpdates'])->name('work-updates.index');
    Route::get('/work-updates/download/pdf', [AgentDashboardController::class, 'downloadWorkUpdatesPdf'])->name('work-updates.download.pdf');
    Route::get('/work-updates/download/csv', [AgentDashboardController::class, 'downloadWorkUpdatesCsv'])->name('work-updates.download.csv');

    // Check-in/Check-out routes
    Route::get('/checkin', [\App\Http\Controllers\Agent\CheckInController::class, 'index'])->name('checkin.index');
    Route::post('/checkin/check-in', [\App\Http\Controllers\Agent\CheckInController::class, 'checkIn'])->name('checkin.check-in');
    Route::post('/checkin/check-out', [\App\Http\Controllers\Agent\CheckInController::class, 'checkOut'])->name('checkin.check-out');

    // Meeting tracking routes
    Route::post('/track-join', [AgentDashboardController::class, 'trackJoin'])->name('track-join');
    Route::post('/meeting/join', [AgentDashboardController::class, 'joinMeeting'])->name('meeting.join');
    Route::post('/meeting/leave', [AgentDashboardController::class, 'leaveMeeting'])->name('meeting.leave');
    Route::get('/meeting/status', [AgentDashboardController::class, 'getMeetingStatus'])->name('meeting.status');
    Route::get('/checkin/status', [\App\Http\Controllers\Agent\CheckInController::class, 'getStatus'])->name('checkin.status');

    // Screen sharing routes
    Route::post('/screen-sharing/start', [AgentDashboardController::class, 'startScreenSharing'])->name('screen-sharing.start');
    Route::post('/screen-sharing/stop', [AgentDashboardController::class, 'stopScreenSharing'])->name('screen-sharing.stop');

    // Draft routes (simplified)
    Route::get('/work-updates/drafts', [AgentDashboardController::class, 'drafts'])->name('work-updates.drafts');
    Route::get('/work-updates/drafts/{draft}/edit', [AgentDashboardController::class, 'editDraft'])->name('work-updates.edit-draft');
    Route::put('/work-updates/drafts/{draft}', [AgentDashboardController::class, 'updateDraft'])->name('work-updates.update-draft');
    Route::delete('/work-updates/drafts/{draft}', [AgentDashboardController::class, 'deleteDraft'])->name('work-updates.delete-draft');
    Route::delete('/work-updates/drafts/{draft}/group', [AgentDashboardController::class, 'deleteDraftGroup'])->name('work-updates.delete-draft-group');
    Route::post('/work-updates/drafts/submit', [AgentDashboardController::class, 'submitDraftGroup'])->name('work-updates.submit-drafts');

    // Agent Client Submissions
    Route::get('/submissions', [\App\Http\Controllers\Agent\ClientSubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/{submission}', [\App\Http\Controllers\Agent\ClientSubmissionController::class, 'show'])->name('submissions.show');
    Route::patch('/submissions/{submission}/status', [\App\Http\Controllers\Agent\ClientSubmissionController::class, 'updateStatus'])->name('submissions.update-status');
    Route::get('/clients/{client}/submissions', [\App\Http\Controllers\Agent\ClientSubmissionController::class, 'getClientSubmissions'])->name('clients.submissions');

    // Agent Clients Management
    Route::get('/clients', [AgentDashboardController::class, 'clients'])->name('clients.index');
    Route::get('/clients/{client}', [AgentDashboardController::class, 'showClient'])->name('clients.show');
    Route::post('/request-otp', [AgentDashboardController::class, 'requestOtp'])->name('request-otp');
    Route::get('/notices', [AgentNoticeController::class, 'index'])->name('notices.index');

    // Support Tickets
    Route::get('/support-tickets', [SupportTicketController::class, 'index'])->name('support-tickets.index');
    Route::get('/support-tickets/{supportTicket}', [SupportTicketController::class, 'show'])->name('support-tickets.show');
    Route::get('/support-tickets/{supportTicket}/messages', [SupportTicketController::class, 'messages'])->name('support-tickets.messages');
    Route::post('/support-tickets/{supportTicket}/message', [SupportTicketController::class, 'storeMessage'])->name('support-tickets.message');
    Route::post('/support-tickets/{supportTicket}/request-close', [SupportTicketController::class, 'requestClose'])->name('support-tickets.request-close');
});

// Client Routes
Route::middleware(['auth', 'role:client', 'canonical.route'])->prefix('client')->name('client.')->group(function () {
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    Route::get('/work-updates', [ClientDashboardController::class, 'workUpdates'])->name('work-updates.index');
    Route::get('/work-updates/{workUpdate}/edit', [ClientDashboardController::class, 'editWorkUpdate'])->name('work-updates.edit');
    Route::put('/work-updates/{workUpdate}', [ClientDashboardController::class, 'updateWorkUpdate'])->name('work-updates.update');
    Route::get('/work-updates/download/pdf', [ClientDashboardController::class, 'downloadPdf'])->name('work-updates.download.pdf');
    Route::get('/work-updates/download/csv', [ClientDashboardController::class, 'downloadCsv'])->name('work-updates.download.csv');
    Route::get('/work-updates/filter', [ClientDashboardController::class, 'filterUpdates'])->name('work-updates.filter');

    // Client Submissions
    Route::resource('submissions', \App\Http\Controllers\Client\ClientSubmissionController::class);

    // Client OTP Verification
    Route::get('/otp/{otpVerification}/submit', [\App\Http\Controllers\Client\OtpController::class, 'submit'])->name('otp.submit');
    Route::post('/otp/{otpVerification}/verify', [\App\Http\Controllers\Client\OtpController::class, 'verify'])->name('otp.verify');

    // Support Tickets
    Route::get('/support-tickets', [SupportTicketController::class, 'index'])->name('support-tickets.index');
    Route::get('/support-tickets/create', [SupportTicketController::class, 'create'])->name('support-tickets.create');
    Route::post('/support-tickets', [SupportTicketController::class, 'store'])->name('support-tickets.store');
    Route::get('/support-tickets/{supportTicket}', [SupportTicketController::class, 'show'])->name('support-tickets.show');
    Route::get('/support-tickets/{supportTicket}/messages', [SupportTicketController::class, 'messages'])->name('support-tickets.messages');
    Route::post('/support-tickets/{supportTicket}/message', [SupportTicketController::class, 'storeMessage'])->name('support-tickets.message');
    Route::post('/support-tickets/{supportTicket}/approve-close', [SupportTicketController::class, 'approveClose'])->name('support-tickets.approve-close');
    Route::post('/support-tickets/{supportTicket}/decline-close', [SupportTicketController::class, 'declineClose'])->name('support-tickets.decline-close');
    Route::post('/support-tickets/{supportTicket}/close', [SupportTicketController::class, 'close'])->name('support-tickets.close');

    // OTP Requests listing for clients
    Route::get('/otp-requests', [\App\Http\Controllers\Client\OtpController::class, 'index'])->name('otp-requests.index');

    // Payment Requests
    Route::post('/payment-requests/{paymentRequest}/mark-paid', [ClientPaymentRequestController::class, 'markPaid'])->name('payment-requests.mark-paid');
    Route::get('/notices', [ClientNoticeController::class, 'index'])->name('notices.index');

    // Client Onboarding
    Route::get('/onboarding', [\App\Http\Controllers\Client\OnboardingController::class, 'create'])->name('onboarding.create');
    Route::post('/onboarding', [\App\Http\Controllers\Client\OnboardingController::class, 'store'])->name('onboarding.store');
});

// Public OTP routes (no authentication required)
Route::get('/otp/{otpVerification}/submit', [\App\Http\Controllers\Client\OtpController::class, 'submit'])->middleware('canonical.route')->name('otp.submit.public');
Route::post('/otp/{otpVerification}/verify', [\App\Http\Controllers\Client\OtpController::class, 'verify'])->name('otp.verify.public');

Route::middleware(['auth', 'canonical.route'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notification routes
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::delete('/notifications/{notification}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/notices/{notice}/dismiss', [NoticeController::class, 'dismiss'])->name('notices.dismiss');
});

// Google OAuth routes
Route::middleware(['auth', 'canonical.route'])->group(function () {
    Route::get('/google/oauth/redirect', [App\Http\Controllers\GoogleOAuthController::class, 'redirect'])->name('google.oauth.redirect');
    Route::get('/google/oauth/callback', [App\Http\Controllers\GoogleOAuthController::class, 'callback'])->name('google.oauth.callback');
    Route::post('/google/oauth/create-meet', [App\Http\Controllers\GoogleOAuthController::class, 'createMeetRoom'])->name('google.oauth.create-meet');
    Route::post('/google/oauth/disconnect', [App\Http\Controllers\GoogleOAuthController::class, 'disconnect'])->name('google.oauth.disconnect');
});

// OAuth test route (for debugging)
Route::get('/oauth-test', function () {
    $client = new \Google\Client();
    $client->setClientId(config('services.google.client_id'));
    $client->setClientSecret(config('services.google.client_secret'));
    $client->setRedirectUri(config('services.google.redirect_uri'));
    $client->setScopes([\Google\Service\Calendar::CALENDAR]);
    $client->setAccessType('offline');
    $client->setApprovalPrompt('force');

    $authUrl = $client->createAuthUrl();

    return view('oauth-test', compact('authUrl'));
})->name('oauth.test');

require __DIR__.'/auth.php';
require __DIR__.'/storage.php';
