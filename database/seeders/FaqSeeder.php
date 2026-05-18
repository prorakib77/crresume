<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class FaqSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (!Schema::hasTable('faqs')) {
            $this->command?->warn('FAQs table not found. Run migrations before seeding FAQs.');

            return;
        }

        $faqs = [
            [
                'question' => 'What is included in your WFH full-service package?',
                'answer' => "Our client service is built around helping you present a stronger remote-work application. Depending on your setup, that can include ATS-focused resume support, tailored cover letter guidance, onboarding, daily work updates inside your dashboard, and ongoing communication with your assigned team.",
            ],
            [
                'question' => 'How do I get started as a new client?',
                'answer' => "Create your account, log in to your client dashboard, and complete the onboarding form. Share your background, target roles, and any resume or career details you already have so the team can start preparing your service correctly.",
            ],
            [
                'question' => 'What information should I submit during onboarding?',
                'answer' => "You should provide the details that help us understand your job target: your work history, target position, preferred industries, skills, resume file if available, and any notes that explain your goals. The more accurate your onboarding details are, the smoother the service becomes.",
            ],
            [
                'question' => 'How will I see my daily work updates?',
                'answer' => "All approved work updates appear in your client dashboard. You can review them by date, filter the list, and export your updates as PDF or CSV whenever you need a record of the jobs worked on for your account.",
            ],
            [
                'question' => 'Why would I receive an OTP or company information request?',
                'answer' => "Sometimes an application needs a verification step from the client side. In that case, your assigned agent may send you a secure OTP request. You can open the request, submit the company name and OTP, and the team will continue the application workflow from there.",
            ],
            [
                'question' => 'How do payment requests work on the client side?',
                'answer' => "If a payment is due, you will see a payment request inside your client area. After you make the payment, you can mark it as paid from your dashboard. The admin team can then review and confirm the request status.",
            ],
            [
                'question' => 'How long does my service stay active?',
                'answer' => "Service length depends on the package or assignment created for your account. If your service has a defined end date, it is tracked in the system. Your dashboard and notices help keep you informed about onboarding, active service status, and any next steps.",
            ],
            [
                'question' => 'Can I contact support if I need help or have a question?',
                'answer' => "Yes. Clients can open support tickets directly from the portal. This is the best place to ask questions about onboarding, service progress, updates, access, or payment-related concerns because the conversation stays attached to your account.",
            ],
            [
                'question' => 'Do I need an existing resume before signing up?',
                'answer' => "No. If you already have a resume, you can upload it during onboarding. If you do not have one ready, you can still provide your background, work history, and goals so the team has the information needed to support your resume and application materials.",
            ],
            [
                'question' => 'Do you guarantee a job offer?',
                'answer' => "No service can honestly guarantee a job offer. What we do provide is a more organized, professional, and better-supported application process for work-from-home opportunities, along with clear visibility into the work being done on your behalf.",
            ],
        ];

        foreach ($faqs as $index => $faq) {
            Faq::query()->updateOrCreate(
                ['question' => $faq['question']],
                [
                    'answer' => $faq['answer'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info('Dummy client FAQs seeded successfully.');
    }
}
