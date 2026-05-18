<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AgentClientAssignment;
use App\Services\NoticeService;
use Illuminate\Support\Facades\Auth;

class NoticeController extends Controller
{
    public function __construct(
        protected NoticeService $noticeService
    ) {
    }

    public function index()
    {
        $user = Auth::user();
        $assignment = AgentClientAssignment::query()
            ->where('client_id', $user->id)
            ->where('is_active', true)
            ->latest('assigned_date')
            ->latest('id')
            ->first();

        $this->noticeService->syncClientServiceNotice($user, $assignment);

        return view('client.notices.index', [
            'notices' => $this->noticeService->getVisibleNoticesPaginated($user),
            'noticeCount' => $this->noticeService->getVisibleNoticeCount($user),
        ]);
    }
}
