<?php

namespace App\Http\Controllers;

use App\Application\Services\ModificationRequestService;
use App\Application\Services\ModificationRequestBreaksService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ModificationRequestRequest;

class ModificationRequestController extends Controller
{
    private ModificationRequestService $modificationRequestService;
    private ModificationRequestBreaksService $modificationRequestBreaksService;

    public function __construct(
        ModificationRequestService $modificationRequestService,
        ModificationRequestBreaksService $modificationRequestBreaksService
    ) {
        $this->modificationRequestService = $modificationRequestService;
        $this->modificationRequestBreaksService = $modificationRequestBreaksService;
    }

    /**
     * 修正申請一覧画面表示
     */
    public function index(): View
    {
        $user = Auth::user();

        // 承認待ちの申請を取得
        $pendingRequests = $this->modificationRequestService->getPendingRequestsByUser($user->id);

        // 承認済みの申請を取得
        $approvedRequests = $this->modificationRequestService->getApprovedRequestsByUser($user->id);

        return view('modification-requests.index', [
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
        ]);
    }

    /**
     * 修正申請作成処理
     */
    public function store(ModificationRequestRequest $request): RedirectResponse
    {
        $user = Auth::user();

        try {
            $validatedData = $request->validated();

            // 修正申請を作成
            $modificationRequest = $this->modificationRequestService->createRequest(
                $validatedData['attendance_id'],
                $user->id,
                [
                    'start_time' => $validatedData['start_time'],
                    'end_time' => $validatedData['end_time'],
                    'remarks' => $validatedData['remarks'],
                ]
            );

            // 休憩時間の修正申請も作成
            if (isset($validatedData['breaks'])) {
                foreach ($validatedData['breaks'] as $breakData) {
                    if ($breakData['start_time'] || $breakData['end_time']) {
                        $this->modificationRequestBreaksService->createRequest(
                            $modificationRequest->id,
                            $breakData
                        );
                    }
                }
            }

            return redirect()->route('modification-requests.index')
                ->with('message', '修正申請を送信しました');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', '修正申請の送信に失敗しました: ' . $e->getMessage())
                ->withInput();
        }
    }
}
