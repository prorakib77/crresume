<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MailchimpService;
use App\Models\MailchimpSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    public function index()
    {
        $mailchimpSettings = MailchimpSetting::getActive();

        if ($mailchimpSettings) {
            $mailchimpStatus = $mailchimpSettings->testConnection();
            $mailchimpInfo = $mailchimpSettings->getAccountInfo();
        } else {
            $mailchimpStatus = false;
            $mailchimpInfo = null;
        }

        return view('admin.settings.index', compact('mailchimpStatus', 'mailchimpInfo', 'mailchimpSettings'));
    }

    public function testMailchimp()
    {
        $mailchimpService = new MailchimpService();
        $status = $mailchimpService->testConnection();
        $info = $mailchimpService->getAccountInfo();

        if ($status) {
            return response()->json([
                'success' => true,
                'message' => 'Mailchimp connection successful!',
                'info' => $info
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Mailchimp connection failed. Please check your API key and server prefix.'
            ]);
        }
    }

    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return back()->with('success', 'Cache cleared successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    public function optimize()
    {
        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');

            return back()->with('success', 'Application optimized successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to optimize application: ' . $e->getMessage());
        }
    }
}
