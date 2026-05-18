<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\User;
use App\Services\NoticeService;
use App\Services\UserCommunicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class NoticeController extends Controller
{
    public function __construct(
        protected NoticeService $noticeService,
        protected UserCommunicationService $communicationService,
    ) {
    }

    public function index()
    {
        $manualNotices = Notice::query()
            ->where('source_type', Notice::SOURCE_MANUAL)
            ->with(['recipient', 'creator'])
            ->latest()
            ->paginate(12);

        $targetUsers = User::query()
            ->whereIn('role_id', [User::ROLE_AGENT, User::ROLE_CLIENT])
            ->orderByRaw('CASE WHEN role_id = ? THEN 0 ELSE 1 END', [User::ROLE_AGENT])
            ->orderBy('name')
            ->get();

        return view('admin.notices.index', [
            'manualNotices' => $manualNotices,
            'targetUsers' => $targetUsers,
            'iconOptions' => $this->iconOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'scope' => ['required', Rule::in(['agent', 'client', 'both', 'specific'])],
            'recipient_user_id' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('scope') === 'specific'),
                Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn('role_id', [User::ROLE_AGENT, User::ROLE_CLIENT])),
            ],
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'icon_class' => 'nullable|string|max:120',
            'custom_icon_class' => 'nullable|string|max:120',
            'background_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'action_url' => 'nullable|string|max:1000',
            'send_email' => ['nullable', 'boolean'],
        ]);

        $recipient = null;
        $audience = $data['scope'];

        if ($data['scope'] === 'specific') {
            $recipient = User::findOrFail($data['recipient_user_id']);
            $audience = $recipient->isAgent() ? Notice::AUDIENCE_AGENT : Notice::AUDIENCE_CLIENT;
        }

        $notice = $this->noticeService->createManualNotice([
            'title' => $data['title'],
            'content' => $data['content'],
            'icon_class' => filled($data['custom_icon_class']) ? $data['custom_icon_class'] : ($data['icon_class'] ?: 'fa-solid fa-circle-info'),
            'background_color' => $data['background_color'],
            'audience' => $audience,
            'recipient_user_id' => $recipient?->id,
            'action_url' => filled($data['action_url']) ? $data['action_url'] : null,
        ], Auth::user());

        $recipients = $this->resolveRecipients($data['scope'], $recipient);
        $failedEmailRecipients = [];
        $shouldSendEmail = $request->boolean('send_email');

        foreach ($recipients as $user) {
            $this->communicationService->notify(
                $user,
                $notice->title,
                $notice->content,
                \App\Models\Notification::TYPE_INFO,
                ['category' => 'admin_notice', 'notice_id' => $notice->id],
                \App\Models\Notification::PRIORITY_NORMAL,
                $notice,
                $notice->action_url ?: route($user->isAgent() ? 'agent.notices.index' : 'client.notices.index')
            );

            if (!$shouldSendEmail) {
                continue;
            }

            $emailSent = $this->communicationService->sendStructuredEmail(
                $user,
                'New Admin Notice: ' . $notice->title,
                'A new notice was added to your account.',
                [$notice->content],
                $notice->action_url ?: route($user->isAgent() ? 'agent.notices.index' : 'client.notices.index'),
                'Open Notices'
            );

            if (!$emailSent) {
                $failedEmailRecipients[] = $user->email ?: ('User #' . $user->id);
            }
        }

        if (!empty($failedEmailRecipients)) {
            return back()->with(
                'warning',
                'Notice created successfully, but ' . count($failedEmailRecipients) . ' email(s) could not be delivered.'
            );
        }

        return back()->with('success', 'Notice created successfully.');
    }

    public function toggle(Notice $notice)
    {
        if ($notice->source_type !== Notice::SOURCE_MANUAL) {
            return back()->with('info', 'Only admin-created notices can be turned on or off from this page.');
        }

        $newState = !$notice->is_active;

        $notice->update([
            'is_active' => $newState,
        ]);

        if ($newState) {
            $notice->dismissals()->delete();
        }

        return back()->with('success', 'Notice status updated.');
    }

    protected function iconOptions(): array
    {
        return [
            ['label' => 'Info', 'value' => 'fa-solid fa-circle-info'],
            ['label' => 'Alert', 'value' => 'fa-solid fa-triangle-exclamation'],
            ['label' => 'Bullhorn', 'value' => 'fa-solid fa-bullhorn'],
            ['label' => 'Bell', 'value' => 'fa-solid fa-bell'],
            ['label' => 'File', 'value' => 'fa-solid fa-file-lines'],
            ['label' => 'Clock', 'value' => 'fa-solid fa-clock'],
            ['label' => 'Shield', 'value' => 'fa-solid fa-shield-halved'],
            ['label' => 'Check', 'value' => 'fa-solid fa-circle-check'],
            ['label' => 'Bolt', 'value' => 'fa-solid fa-bolt'],
            ['label' => 'Flag', 'value' => 'fa-solid fa-flag'],
        ];
    }

    protected function resolveRecipients(string $scope, ?User $specificRecipient = null)
    {
        if ($scope === 'specific' && $specificRecipient) {
            return collect([$specificRecipient]);
        }

        if ($scope === 'agent') {
            return User::query()->where('role_id', User::ROLE_AGENT)->get();
        }

        if ($scope === 'client') {
            return User::query()->where('role_id', User::ROLE_CLIENT)->get();
        }

        return User::query()
            ->whereIn('role_id', [User::ROLE_AGENT, User::ROLE_CLIENT])
            ->get();
    }
}
