<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Services\NoticeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NoticeController extends Controller
{
    public function __construct(
        protected NoticeService $noticeService
    ) {
    }

    public function dismiss(Notice $notice): JsonResponse
    {
        $user = Auth::user();

        $isVisible = Notice::query()
            ->whereKey($notice->id)
            ->visibleToUser($user)
            ->exists();

        if (!$isVisible) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $dismissal = $this->noticeService->dismissForUser($notice, $user);

        return response()->json([
            'success' => true,
            'dismissed_until' => $dismissal->dismissed_until?->toIso8601String(),
        ]);
    }
}
