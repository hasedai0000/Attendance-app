<?php

namespace App\Application\Services;

use App\Domain\Attendance\Repositories\AttendanceRepositoryInterface;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceService
{
  private AttendanceRepositoryInterface $attendanceRepository;

  public function __construct(AttendanceRepositoryInterface $attendanceRepository)
  {
    $this->attendanceRepository = $attendanceRepository;
  }

  /**
   * 今日の勤怠情報を取得
   */
  public function getTodayAttendance(string $userId): ?Attendance
  {
    return $this->attendanceRepository->findByUserAndDate($userId, Carbon::today());
  }

  /**
   * 現在のステータスを取得
   */
  public function getCurrentStatus(string $userId): string
  {
    $attendance = $this->getTodayAttendance($userId);

    if (!$attendance) {
      return 'not_working'; // 勤務外
    }

    return $attendance->status;
  }

  /**
   * 出勤処理
   */
  public function startWork(string $userId): Attendance
  {
    $today = Carbon::today();
    $attendance = $this->attendanceRepository->findByUserAndDate($userId, $today);

    // 既に出勤済みの場合はエラー
    if ($attendance && $attendance->start_time) {
      throw new \Exception('本日は既に出勤済みです');
    }

    if (!$attendance) {
      // 新規作成
      $attendance = $this->attendanceRepository->create([
        'user_id' => $userId,
        'date' => $today,
        'start_time' => Carbon::now(),
        'status' => 'working',
      ]);
    } else {
      // 更新
      $attendance = $this->attendanceRepository->update($attendance->id, [
        'start_time' => Carbon::now(),
        'status' => 'working',
      ]);
    }

    return $attendance;
  }

  /**
   * 退勤処理
   */
  public function endWork(string $userId): Attendance
  {
    $attendance = $this->getTodayAttendance($userId);

    if (!$attendance || !$attendance->start_time) {
      throw new \Exception('出勤記録がありません');
    }

    if ($attendance->end_time) {
      throw new \Exception('既に退勤済みです');
    }

    // 休憩中の場合はエラー
    if ($attendance->status === 'on_break') {
      throw new \Exception('休憩中のため退勤できません。休憩を終了してください');
    }

    return $this->attendanceRepository->update($attendance->id, [
      'end_time' => Carbon::now(),
      'status' => 'finished',
    ]);
  }

  /**
   * 月次勤怠一覧を取得
   */
  public function getMonthlyAttendances(string $userId, string $month): array
  {
    $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
    $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

    return $this->attendanceRepository->findByUserAndDateRange($userId, $startDate, $endDate);
  }

  /**
   * 勤怠詳細を取得
   */
  public function getAttendanceDetail(string $attendanceId): Attendance
  {
    return $this->attendanceRepository->findById($attendanceId);
  }

  /**
   * 今日の全ユーザーの勤怠情報を取得
   */
  public function getTodayAttendances(): array
  {
    return $this->attendanceRepository->findByDate(Carbon::today());
  }

  /**
   * 指定日の全ユーザーの勤怠情報を取得
   */
  public function getAttendancesByDate(Carbon $date): array
  {
    return $this->attendanceRepository->findByDate($date);
  }

  /**
   * 勤怠情報を更新
   */
  public function updateAttendance(string $attendanceId, array $data): Attendance
  {
    return $this->attendanceRepository->update($attendanceId, $data);
  }

  /**
   * ステータスを日本語に変換
   */
  public function getStatusLabel(string $status): string
  {
    return match ($status) {
      'not_working' => '勤務外',
      'working' => '出勤中',
      'on_break' => '休憩中',
      'finished' => '退勤済',
      default => '不明',
    };
  }
}
