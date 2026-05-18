<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormSubmissionMail;
use App\Models\CustomizationSetting;
use App\Models\Faq;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class PublicPageController extends Controller
{
    public function reviews(): View
    {
        $settings = CustomizationSetting::getAllActive();
        $siteName = site_name();
        $siteLogo = site_logo();
        $siteFavicon = site_favicon();

        $reviews = Review::query()
            ->orderBy('sort_order')
            ->orderByDesc('is_active')
            ->orderBy('id')
            ->paginate(12);

        return view('public.reviews', compact('settings', 'siteName', 'siteLogo', 'siteFavicon', 'reviews'));
    }

    public function contact(): View
    {
        $settings = CustomizationSetting::getAllActive();
        $siteName = site_name();
        $siteLogo = site_logo();
        $siteFavicon = site_favicon();

        return view('public.contact', compact('settings', 'siteName', 'siteLogo', 'siteFavicon'));
    }

    public function faqs(): View
    {
        $settings = CustomizationSetting::getAllActive();
        $siteName = site_name();
        $siteLogo = site_logo();
        $siteFavicon = site_favicon();

        $faqs = Faq::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('public.faqs', compact('settings', 'siteName', 'siteLogo', 'siteFavicon', 'faqs'));
    }

    public function clientGuide(): View
    {
        $settings = CustomizationSetting::getAllActive();
        $siteName = site_name();
        $siteLogo = site_logo();
        $siteFavicon = site_favicon();

        $guide = [
            'badge' => CustomizationSetting::getValue('client_guide_badge'),
            'title' => CustomizationSetting::getValue('client_guide_title'),
            'subtitle' => CustomizationSetting::getValue('client_guide_subtitle'),
            'intro_title' => CustomizationSetting::getValue('client_guide_intro_title'),
            'intro_text' => CustomizationSetting::getValue('client_guide_intro_text'),
            'support_title' => CustomizationSetting::getValue('client_guide_support_title'),
            'support_text' => CustomizationSetting::getValue('client_guide_support_text'),
            'primary_label' => CustomizationSetting::getValue('client_guide_primary_label'),
            'primary_link' => CustomizationSetting::getValue('client_guide_primary_link'),
            'secondary_label' => CustomizationSetting::getValue('client_guide_secondary_label'),
            'secondary_link' => CustomizationSetting::getValue('client_guide_secondary_link'),
        ];

        $steps = [];

        foreach (range(1, 6) as $stepNumber) {
            $steps[] = [
                'number' => $stepNumber,
                'eyebrow' => CustomizationSetting::getValue("client_guide_step_{$stepNumber}_eyebrow"),
                'title' => CustomizationSetting::getValue("client_guide_step_{$stepNumber}_title"),
                'body' => CustomizationSetting::getValue("client_guide_step_{$stepNumber}_body"),
            ];
        }

        return view('public.client-guide', compact('settings', 'siteName', 'siteLogo', 'siteFavicon', 'guide', 'steps'));
    }

    public function privacyPolicy(): View
    {
        return $this->renderPolicyPage(
            'privacy_policy',
            'Privacy Policy',
            'How we collect, use, store, and protect the information shared through your client portal and service workflow.',
            'privacy_policy_content'
        );
    }

    public function termsOfService(): View
    {
        return $this->renderPolicyPage(
            'terms_of_service',
            'Terms of Service',
            'The service terms that apply when you use this website, create an account, or purchase client support from our team.',
            'terms_of_service_content'
        );
    }

    public function bookingPolicy(): View
    {
        return $this->renderPolicyPage(
            'booking_policy',
            'Booking Policy',
            'Scheduling, booking, and service management terms that apply when you reserve or continue client services with our team.',
            'booking_policy_content'
        );
    }

    public function refundPolicy(): View
    {
        return $this->renderPolicyPage(
            'refund_policy',
            'Refund Policy',
            'Clear payment and refund terms for clients using our portal, onboarding process, and remote-career support service.',
            'refund_policy_content'
        );
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['required', 'string', 'max:190'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        try {
            Mail::to($this->resolveAdminEmail())->send(new ContactFormSubmissionMail($validated));
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Unable to send your message right now. Please try again.');
        }

        return back()->with('success', 'Your message has been sent successfully.');
    }

    private function resolveAdminEmail(): string
    {
        $adminEmail = User::query()
            ->whereIn('role_id', [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN])
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('role_id')
            ->value('email');

        return $adminEmail ?: (string) config('mail.from.address');
    }

    private function renderPolicyPage(string $settingPrefix, string $fallbackTitle, string $fallbackSubtitle, string $contentKey): View
    {
        $settings = CustomizationSetting::getAllActive();
        $siteName = site_name();
        $siteLogo = site_logo();
        $siteFavicon = site_favicon();
        $titleKey = "{$settingPrefix}_title";
        $subtitleKey = "{$settingPrefix}_subtitle";
        $metaTextKey = "{$settingPrefix}_meta_text";

        $relatedSettings = collect([
            $settings->get($contentKey),
            $settings->get($titleKey),
            $settings->get($subtitleKey),
            $settings->get($metaTextKey),
        ])->filter();

        $latestSetting = $relatedSettings
            ->sortByDesc(fn ($setting) => $setting?->updated_at?->getTimestamp() ?? 0)
            ->first();

        $updatedAt = $latestSetting?->updated_at;
        $formattedDate = $updatedAt?->format('F j, Y') ?? now()->format('F j, Y');
        $titleFallback = (string) CustomizationSetting::defaultValue($titleKey, $fallbackTitle);
        $subtitleFallback = (string) CustomizationSetting::defaultValue($subtitleKey, $fallbackSubtitle);
        $contentFallback = (string) CustomizationSetting::defaultValue($contentKey, '');
        $metaFallback = (string) CustomizationSetting::defaultValue($metaTextKey, 'Last updated {date}');
        $metaTemplate = (string) CustomizationSetting::getValue($metaTextKey, $metaFallback);

        $policyPage = [
            'title' => CustomizationSetting::getValue($titleKey, $titleFallback),
            'subtitle' => CustomizationSetting::getValue($subtitleKey, $subtitleFallback),
            'content' => CustomizationSetting::getValue($contentKey, $contentFallback),
            'meta_text' => trim(str_replace('{date}', $formattedDate, $metaTemplate)),
        ];

        return view('public.policy-page', compact('settings', 'siteName', 'siteLogo', 'siteFavicon', 'policyPage'));
    }
}
