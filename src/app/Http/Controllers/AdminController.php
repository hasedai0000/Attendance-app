<?php

namespace App\Http\Controllers;

use App\Application\Services\AttendanceService;
use App\Application\Services\ModificationRequestService;
use App\Application\Services\UserService;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;

class AdminController extends Controller
{
    private AttendanceService $attendanceService;
    private ModificationRequestService $modificationRequestService;
    private UserService $userService;

    public function __construct(
        AttendanceService $attendanceService,
        ModificationRequestService $modificationRequestService,
        UserService $userService
    ) {
        $this->attendanceService = $attendanceService;
        $this->modificationRequestService = $modificationRequestService;
        $this->userService = $userService;
    }

    /**
     * 日次勤怠一覧（管理者ダッシュボード）
     */
    public function dailyAttendance(Request $request): View
    {
        try {
            $dateString = $request->get('date', Carbon::today()->format('Y-m-d'));
            $targetDate = Carbon::createFromFormat('Y-m-d', $dateString);

            // 指定日の勤怠データ
            $attendances = $this->attendanceService->getAttendancesByDate($targetDate);

            // 今日の勤怠統計（ダッシュボード用）
            $todayAttendances = $this->attendanceService->getTodayAttendances();
            $pendingRequests = $this->modificationRequestService->getPendingModificationRequests();

            return view('admin.daily-attendance', [
                'attendances' => $attendances,
                'date' => $targetDate,
                'todayAttendances' => $todayAttendances,
                'pendingRequests' => $pendingRequests,
                'today' => Carbon::today(),
            ]);
        } catch (\Exception $e) {
            return view('admin.daily-attendance', [
                'attendances' => [],
                'date' => Carbon::today(),
                'todayAttendances' => [],
                'pendingRequests' => [],
                'today' => Carbon::today(),
            ])->with('error', 'データの取得に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * スタッフ一覧
     */
    public function staffList(): View
    {
        try {
            $staff = $this->userService->getAllUsers();

            return view('admin.staff-list', [
                'staff' => $staff,
            ]);
        } catch (\Exception $e) {
            return view('admin.staff-list', [
                'staff' => [],
            ])->with('error', 'データの取得に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * スタッフ月次勤怠一覧
     */
    public function staffAttendance(Request $request, string $id): View
    {
        try {
            $monthString = $request->get('month', Carbon::now()->format('Y-m'));
            $user = $this->userService->getUserById($id);

            if (!$user) {
                abort(404, 'ユーザーが見つかりません');
            }

            $attendances = $this->attendanceService->getMonthlyAttendances($id, $monthString);

            return view('admin.staff-attendance', [
                'attendances' => $attendances,
                'user' => $user,
                'month' => $monthString,
            ]);
        } catch (\Exception $e) {
            return view('admin.staff-attendance', [
                'attendances' => [],
                'user' => null,
                'month' => Carbon::now()->format('Y-m'),
            ])->with('error', 'データの取得に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * 修正申請一覧（管理者）
     */
    public function modificationRequests(): View
    {
        try {
            $pendingRequests = $this->modificationRequestService->getPendingModificationRequests();
            $approvedRequests = $this->modificationRequestService->getApprovedModificationRequests();

            return view('admin.modification-requests', [
                'pendingRequests' => $pendingRequests,
                'approvedRequests' => $approvedRequests,
            ]);
        } catch (\Exception $e) {
            return view('admin.modification-requests', [
                'pendingRequests' => [],
                'approvedRequests' => [],
            ])->with('error', 'データの取得に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * 修正申請承認
     */
    public function approveModificationRequest(string $requestId): RedirectResponse
    {
        try {
            $this->modificationRequestService->approveModificationRequest($requestId);

            return redirect()->back()->with('message', '修正申請を承認しました');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '承認に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * 修正申請詳細画面（管理者）
     */
    public function modificationRequestDetail(string $requestId): View
    {
        try {
            $modificationRequest = $this->modificationRequestService->getModificationRequestById($requestId);

            if (!$modificationRequest) {
                abort(404, '修正申請が見つかりません');
            }

            return view('admin.modification-request-detail', [
                'modificationRequest' => $modificationRequest,
            ]);
        } catch (\Exception $e) {
            abort(404, '修正申請の取得に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * 勤怠情報を直接更新
     */
    public function updateAttendance(AdminAttendanceUpdateRequest $request, string $id): RedirectResponse
    {
        try {
            $attendance = $this->attendanceService->getAttendanceDetail($id);

            if (!$attendance) {
                abort(404, '勤怠記録が見つかりません');
            }

            // 勤怠情報を更新
            $updateData = [
                'start_time' => $request->input('start_time') ? Carbon::createFromFormat('H:i', $request->input('start_time'))->setDateFrom($attendance->date) : null,
                'end_time' => $request->input('end_time') ? Carbon::createFromFormat('H:i', $request->input('end_time'))->setDateFrom($attendance->date) : null,
                'remarks' => $request->input('remarks'),
            ];

            $this->attendanceService->updateAttendance($id, $updateData);

            // 休憩時間を更新
            if ($request->has('breaks')) {
                $this->attendanceService->updateBreaks($id, $request->input('breaks'));
            }

            return redirect()->route('admin.attendance.daily')->with('message', '勤怠情報を更新しました');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '更新に失敗しました: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * CSV出力
     */
    public function exportCsv(Request $request, string $userId)
    {
        try {
            $monthString = $request->get('month', Carbon::now()->format('Y-m'));
            $user = $this->userService->getUserById($userId);

            if (!$user) {
                abort(404, 'ユーザーが見つかりません');
            }

            $attendances = $this->attendanceService->getMonthlyAttendances($userId, $monthString);

            $filename = "attendance_{$user->name}_{$monthString}.csv";

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($attendances, $user) {
                $file = fopen('php://output', 'w');

                // BOMを追加してExcelで文字化けを防ぐ
                fwrite($file, "\xEF\xBB\xBF");

                // ヘッダー行
                fputcsv($file, [
                    '日付',
                    '出勤時刻',
                    '退勤時刻',
                    '休憩時間',
                    '勤務時間',
                    '備考'
                ]);

                // データ行
                foreach ($attendances as $attendance) {
                    $breakTime = '0:00';
                    $workTime = '0:00';

                    // 休憩時間計算
                    if (isset($attendance['breaks']) && count($attendance['breaks']) > 0) {
                        $totalBreakMinutes = 0;
                        foreach ($attendance['breaks'] as $break) {
                            if ($break['start_time'] && $break['end_time']) {
                                $start = Carbon::parse($break['start_time']);
                                $end = Carbon::parse($break['end_time']);
                                $totalBreakMinutes += $start->diffInMinutes($end);
                            }
                        }
                        $hours = intval($totalBreakMinutes / 60);
                        $minutes = $totalBreakMinutes % 60;
                        $breakTime = sprintf('%d:%02d', $hours, $minutes);
                    }

                    // 勤務時間計算
                    if ($attendance['start_time'] && $attendance['end_time']) {
                        $start = Carbon::parse($attendance['start_time']);
                        $end = Carbon::parse($attendance['end_time']);
                        $workMinutes = $start->diffInMinutes($end);

                        // 休憩時間を引く
                        if (isset($attendance['breaks'])) {
                            foreach ($attendance['breaks'] as $break) {
                                if ($break['start_time'] && $break['end_time']) {
                                    $breakStart = Carbon::parse($break['start_time']);
                                    $breakEnd = Carbon::parse($break['end_time']);
                                    $workMinutes -= $breakStart->diffInMinutes($breakEnd);
                                }
                            }
                        }

                        $workHours = intval($workMinutes / 60);
                        $workMins = $workMinutes % 60;
                        $workTime = sprintf('%d:%02d', $workHours, $workMins);
                    }

                    fputcsv($file, [
                        Carbon::parse($attendance['date'])->format('Y-m-d'),
                        $attendance['start_time'] ? Carbon::parse($attendance['start_time'])->format('H:i') : '',
                        $attendance['end_time'] ? Carbon::parse($attendance['end_time'])->format('H:i') : '',
                        $breakTime,
                        $workTime,
                        $attendance['remarks'] ?? ''
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'CSV出力に失敗しました: ' . $e->getMessage());
        }
    }
}
