<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
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

        return view('agent.notices.index', [
            'notices' => $this->noticeService->getVisibleNoticesPaginated($user),
            'noticeCount' => $this->noticeService->getVisibleNoticeCount($user),
        ]);
    }
}
