<?php

namespace App\Application\Services;

use App\Domain\Breaks\Repositories\BreaksRepositoryInterface;
use App\Domain\Attendance\Repositories\AttendanceRepositoryInterface;
use App\Models\Breaks;
use Carbon\Carbon;

class BreaksService
{
  private BreaksRepositoryInterface $breaksRepository;
  private AttendanceRepositoryInterface $attendanceRepository;

  public function __construct(
    BreaksRepositoryInterface $breaksRepository,
    AttendanceRepositoryInterface $attendanceRepository
  ) {
    $this->breaksRepository = $breaksRepository;
    $this->attendanceRepository = $attendanceRepository;
  }

  /**
   * 休憩開始処理
   */
  public function startBreak(string $userId): Breaks
  {
    $today = Carbon::today();
    $attendance = $this->attendanceRepository->findByUserAndDate($userId, $today);

    if (!$attendance || $attendance->status !== 'working') {
      throw new \Exception('出勤中でないため休憩を開始できません');
    }

    // 既に休憩中の場合はエラー
    if ($attendance->status === 'on_break') {
      throw new \Exception('既に休憩中です');
    }

    // 休憩レコードを作成
    $break = $this->breaksRepository->create([
      'attendance_id' => $attendance->id,
      'start_time' => Carbon::now(),
    ]);

    // 出勤ステータスを休憩中に変更
    $this->attendanceRepository->update($attendance->id, [
      'status' => 'on_break',
    ]);

    return $break;
  }

  /**
   * 休憩終了処理
   */
  public function endBreak(string $userId): Breaks
  {
    $today = Carbon::today();
    $attendance = $this->attendanceRepository->findByUserAndDate($userId, $today);

    if (!$attendance || $attendance->status !== 'on_break') {
      throw new \Exception('休憩中でないため休憩を終了できません');
    }

    // 最新の未終了休憩を取得
    $activeBreak = $this->breaksRepository->findActiveBreakByAttendance($attendance->id);

    if (!$activeBreak) {
      throw new \Exception('開始中の休憩が見つかりません');
    }

    // 休憩終了時刻を設定
    $break = $this->breaksRepository->update($activeBreak->id, [
      'end_time' => Carbon::now(),
    ]);

    // 出勤ステータスを勤務中に戻す
    $this->attendanceRepository->update($attendance->id, [
      'status' => 'working',
    ]);

    return $break;
  }

  /**
   * 勤怠IDに紐づく休憩一覧を取得
   */
  public function getBreaksByAttendance(string $attendanceId): array
  {
    return $this->breaksRepository->findByAttendanceId($attendanceId);
  }

  /**
   * 休憩時間の合計を計算（分単位）
   */
  public function calculateTotalBreakMinutes(array $breaks): int
  {
    $totalMinutes = 0;

    foreach ($breaks as $break) {
      if ($break->start_time && $break->end_time) {
        $start = Carbon::parse($break->start_time);
        $end = Carbon::parse($break->end_time);
        $totalMinutes += $start->diffInMinutes($end);
      }
    }

    return $totalMinutes;
  }
}
