<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\User;
use App\Services\SupportTicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SupportTicketController extends Controller
{
    public function index()
    {
        return view('support-tickets.index');
    }

    public function create()
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isClient()) {
            abort(403);
        }

        return view('support-tickets.create', [
            'openComposer' => true,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isClient()) {
            abort(403);
        }

        $rules = [
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ];

        if ($user->isAdmin()) {
            $rules['client_id'] = [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role_id', User::ROLE_CLIENT)),
            ];
            $rules['agent_id'] = [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role_id', User::ROLE_AGENT)),
            ];
        }

        $data = $request->validate($rules);

        $ticket = $this->service()->createTicket($user, $data);

        return redirect()
            ->route($this->service()->routePrefix($user) . '.support-tickets.show', $ticket)
            ->with('success', 'Support ticket created successfully.');
    }

    public function show(Request $request, SupportTicket $supportTicket)
    {
        $this->service()->authorize($supportTicket, Auth::user());

        $canonicalUrl = route($this->service()->routePrefix(Auth::user()) . '.support-tickets.show', $supportTicket);

        if ($request->url() !== $canonicalUrl) {
            return redirect()->to($canonicalUrl, 301);
        }

        return view('support-tickets.show', [
            'supportTicket' => $supportTicket->load(['client', 'agent']),
        ]);
    }

    public function messages(Request $request, SupportTicket $supportTicket)
    {
        $user = Auth::user();

        $this->service()->authorize($supportTicket, $user);

        $afterId = (int) $request->integer('after_id');

        $messages = $supportTicket->messages()
            ->with('sender')
            ->when($afterId > 0, fn ($query) => $query->where('id', '>', $afterId))
            ->orderBy('id')
            ->get()
            ->map(function (SupportTicketMessage $message) use ($user) {
                return [
                    'id' => $message->id,
                    'message' => $message->message,
                    'display_name' => $this->service()->displaySenderName($message, $user),
                    'is_mine' => (int) $message->sender_id === (int) $user->id,
                    'created_at' => $message->created_at?->toIso8601String(),
                    'created_at_human' => $message->created_at?->diffForHumans(),
                ];
            });

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function storeMessage(Request $request, SupportTicket $supportTicket)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $this->service()->sendMessage(Auth::user(), $supportTicket, $data['message']);

        return back()->with('success', 'Message sent successfully.');
    }

    public function assignAgent(Request $request, SupportTicket $supportTicket)
    {
        $data = $request->validate([
            'agent_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role_id', User::ROLE_AGENT)),
            ],
        ]);

        $this->service()->assignAgent(Auth::user(), $supportTicket, $data['agent_id'] ?? null);

        return back()->with('success', 'Agent assignment updated.');
    }

    public function requestClose(Request $request, SupportTicket $supportTicket)
    {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->service()->requestClose(Auth::user(), $supportTicket, $data['note'] ?? null);

        return back()->with('success', 'Close request sent to the client.');
    }

    public function approveClose(SupportTicket $supportTicket)
    {
        $this->service()->approveClose(Auth::user(), $supportTicket);

        return back()->with('success', 'Ticket closed successfully.');
    }

    public function declineClose(SupportTicket $supportTicket)
    {
        $this->service()->declineClose(Auth::user(), $supportTicket);

        return back()->with('success', 'Close request declined.');
    }

    public function close(SupportTicket $supportTicket)
    {
        $this->service()->close(Auth::user(), $supportTicket);

        return back()->with('success', 'Ticket closed successfully.');
    }

    protected function service(): SupportTicketService
    {
        return app(SupportTicketService::class);
    }
}
