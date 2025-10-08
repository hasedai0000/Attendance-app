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
        // 認証されたユーザーが管理者かどうかを判定
        if (Auth::user()->is_admin) {
            // 承認待ちの申請を取得
            $pendingRequests = $this->modificationRequestService->getPendingModificationRequests();
            // 承認済みの申請を取得
            $approvedRequests = $this->modificationRequestService->getApprovedModificationRequests();
        } else {
            $user = Auth::user();
            // 承認待ちの申請を取得
            $pendingRequests = $this->modificationRequestService->getPendingRequestsByUser($user->id);

            // 承認済みの申請を取得
            $approvedRequests = $this->modificationRequestService->getApprovedRequestsByUser($user->id);
        }

        return view('modification-requests.index', [
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
        ]);
    }

    /**
     * 修正申請詳細画面表示
     */
    public function show(string $id): View
    {
        $user = Auth::user();

        // 申請詳細を取得
        $modificationRequest = $this->modificationRequestService->getModificationRequestDetail($id);

        if (!$modificationRequest) {
            abort(404, '修正申請が見つかりません');
        }

        // ユーザーが自分の申請かどうかを確認
        if ($modificationRequest->user_id !== $user->id) {
            abort(403, 'この申請を閲覧する権限がありません');
        }

        return view('modification-requests.show', [
            'modificationRequest' => $modificationRequest,
            'user' => $user,
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
