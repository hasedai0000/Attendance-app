<?php

namespace App\Http\Controllers;

use App\Application\Services\AttendanceService;
use App\Application\Services\BreaksService;
use App\Application\Services\ModificationRequestService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    private AttendanceService $attendanceService;
    private BreaksService $breaksService;
    private ModificationRequestService $modificationRequestService;

    public function __construct(
        AttendanceService $attendanceService,
        BreaksService $breaksService,
        ModificationRequestService $modificationRequestService
    ) {
        $this->attendanceService = $attendanceService;
        $this->breaksService = $breaksService;
        $this->modificationRequestService = $modificationRequestService;
    }

    /**
     * 勤怠打刻画面表示
     */
    public function index(): View
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 今日の勤怠情報を取得
        $attendance = $this->attendanceService->getTodayAttendance($user->id);

        // 現在のステータスを取得
        $currentStatus = $this->attendanceService->getCurrentStatus($user->id);

        return view('attendance.index', [
            'user' => $user,
            'attendance' => $attendance,
            'currentStatus' => $currentStatus,
            'currentDateTime' => Carbon::now(),
        ]);
    }

    /**
     * 出勤処理
     */
    public function startWork(): RedirectResponse
    {
        $user = Auth::user();

        try {
            $this->attendanceService->startWork($user->id);
            return redirect()->route('attendance.index')->with('message', '出勤を記録しました');
        } catch (\Exception $e) {
            return redirect()->route('attendance.index')->with('error', $e->getMessage());
        }
    }

    /**
     * 休憩開始処理
     */
    public function startBreak(): RedirectResponse
    {
        $user = Auth::user();

        try {
            $this->breaksService->startBreak($user->id);
            return redirect()->route('attendance.index')->with('message', '休憩を開始しました');
        } catch (\Exception $e) {
            return redirect()->route('attendance.index')->with('error', $e->getMessage());
        }
    }

    /**
     * 休憩終了処理
     */
    public function endBreak(): RedirectResponse
    {
        $user = Auth::user();

        try {
            $this->breaksService->endBreak($user->id);
            return redirect()->route('attendance.index')->with('message', '休憩を終了しました');
        } catch (\Exception $e) {
            return redirect()->route('attendance.index')->with('error', $e->getMessage());
        }
    }

    /**
     * 退勤処理
     */
    public function endWork(): RedirectResponse
    {
        $user = Auth::user();

        try {
            $this->attendanceService->endWork($user->id);
            return redirect()->route('attendance.index')->with('message', 'お疲れ様でした。');
        } catch (\Exception $e) {
            return redirect()->route('attendance.index')->with('error', $e->getMessage());
        }
    }

    /**
     * 勤怠一覧画面表示
     */
    public function list(Request $request): View
    {
        $user = Auth::user();
        $month = $request->get('month', Carbon::now()->format('Y-m'));

        $attendances = $this->attendanceService->getMonthlyAttendances($user->id, $month);

        return view('attendance.list', [
            'attendances' => $attendances,
            'month' => $month,
        ]);
    }

    /**
     * 勤怠詳細画面表示（一般ユーザー・管理者共通）
     * Cursor Rulesに従って /attendance/{id} を使用
     */
    public function detail($id): View
    {
        $attendance = $this->attendanceService->getAttendanceDetail($id);

        if (!$attendance) {
            abort(404, '勤怠情報が見つかりません');
        }

        // 認証されたユーザーが管理者かどうかを判定
        if (Auth::user()->is_admin) {
            // 管理者の場合は管理者用ビューを返す
            return view('admin.attendance-detail', [
                'attendance' => $attendance,
            ]);
        } else {
            // 一般ユーザーの場合は一般ユーザー用ビューを返す
            $breaks = $this->breaksService->getBreaksByAttendance($id);

            // 申請中の修正申請を取得
            $modificationRequest = $this->modificationRequestService->getPendingRequestByAttendanceId($id);

            return view('attendance.detail', [
                'attendance' => $attendance,
                'breaks' => $breaks,
                'modificationRequest' => $modificationRequest,
            ]);
        }
    }
}
