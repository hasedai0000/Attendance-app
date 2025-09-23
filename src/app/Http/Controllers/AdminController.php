<?php

namespace App\Http\Controllers;

use App\Application\Services\AttendanceService;
use App\Application\Services\ModificationRequestService;
use App\Application\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
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
   * 管理者ダッシュボード
   */
  public function index(): View
  {
    $today = Carbon::today();

    // 今日の勤怠統計
    $todayAttendances = $this->attendanceService->getTodayAttendances();
    $pendingRequests = $this->modificationRequestService->getPendingModificationRequests();

    return view('admin.index', [
      'todayAttendances' => $todayAttendances,
      'pendingRequests' => $pendingRequests,
      'today' => $today,
    ]);
  }

  /**
   * 日次勤怠一覧
   */
  public function dailyAttendance(Request $request): View
  {
    $date = $request->get('date', Carbon::today()->format('Y-m-d'));
    $targetDate = Carbon::createFromFormat('Y-m-d', $date);

    $attendances = $this->attendanceService->getAttendancesByDate($targetDate);

    return view('admin.daily-attendance', [
      'attendances' => $attendances,
      'date' => $targetDate,
    ]);
  }

  /**
   * スタッフ一覧
   */
  public function staffList(): View
  {
    $staff = $this->userService->getAllUsers();

    return view('admin.staff-list', [
      'staff' => $staff,
    ]);
  }

  /**
   * スタッフ月次勤怠一覧
   */
  public function staffAttendance(Request $request, string $userId): View
  {
    $month = $request->get('month', Carbon::now()->format('Y-m'));
    $user = $this->userService->getUserById($userId);

    $attendances = $this->attendanceService->getMonthlyAttendances($userId, $month);

    return view('admin.staff-attendance', [
      'attendances' => $attendances,
      'user' => $user,
      'month' => $month,
    ]);
  }

  /**
   * 勤怠情報修正（管理者）
   */
  public function updateAttendance(Request $request, string $attendanceId): RedirectResponse
  {
    $request->validate([
      'start_time' => 'nullable|date_format:H:i',
      'end_time' => 'nullable|date_format:H:i|after:start_time',
      'remarks' => 'required|string|max:1000',
    ], [
      'start_time.date_format' => '出勤時間は正しい時刻形式で入力してください',
      'end_time.date_format' => '退勤時間は正しい時刻形式で入力してください',
      'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
      'remarks.required' => '備考を記入してください',
      'remarks.string' => '備考は文字列で入力してください',
      'remarks.max' => '備考は1000文字以内で入力してください',
    ]);

    try {
      $updateData = [
        'remarks' => $request->remarks,
      ];

      if ($request->start_time) {
        $updateData['start_time'] = $request->start_time;
      }
      if ($request->end_time) {
        $updateData['end_time'] = $request->end_time;
      }

      $this->attendanceService->updateAttendance($attendanceId, $updateData);

      return redirect()->back()->with('message', '勤怠情報を修正しました');
    } catch (\Exception $e) {
      return redirect()->back()->with('error', '修正に失敗しました: ' . $e->getMessage());
    }
  }

  /**
   * 修正申請一覧（管理者）
   */
  public function modificationRequests(): View
  {
    $pendingRequests = $this->modificationRequestService->getPendingModificationRequests();
    $approvedRequests = $this->modificationRequestService->getApprovedModificationRequests();

    return view('admin.modification-requests', [
      'pendingRequests' => $pendingRequests,
      'approvedRequests' => $approvedRequests,
    ]);
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
   * CSV出力
   */
  public function exportCsv(Request $request, string $userId)
  {
    $month = $request->get('month', Carbon::now()->format('Y-m'));
    $user = $this->userService->getUserById($userId);
    $attendances = $this->attendanceService->getMonthlyAttendances($userId, $month);

    $filename = "attendance_{$user->name}_{$month}.csv";

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
  }

  /**
   * 勤怠詳細画面（管理者）
   */
  public function attendanceDetail(string $attendanceId): View
  {
    $attendance = $this->attendanceService->getAttendanceDetail($attendanceId);

    if (!$attendance) {
      abort(404, '勤怠情報が見つかりません');
    }

    return view('admin.attendance-detail', [
      'attendance' => $attendance,
    ]);
  }
}
