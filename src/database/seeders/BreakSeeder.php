<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BreakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('休憩データを作成中...');

        // 既存の勤怠IDを取得
        $attendances = DB::table('attendances')->get();

        if ($attendances->isEmpty()) {
            $this->command->error('勤怠データが見つかりません。AttendanceSeederを先に実行してください。');
            return;
        }

        $createdCount = 0;

        foreach ($attendances as $attendance) {
            // 各勤怠に対して休憩データを作成
            $breaks = $this->generateBreaksForAttendance($attendance);

            if (!empty($breaks)) {
                try {
                    DB::table('breaks')->insert($breaks);
                    $createdCount += count($breaks);
                    $this->command->info("勤怠ID {$attendance->id} に休憩データを " . count($breaks) . " 件作成しました。");
                } catch (\Exception $e) {
                    $this->command->error("勤怠ID {$attendance->id} の休憩データ作成に失敗しました: " . $e->getMessage());
                }
            }
        }

        $this->command->info("休憩データ作成完了: 合計 {$createdCount} 件作成しました。");
    }

    /**
     * 勤怠データに基づいて休憩データを生成
     *
     * @param object $attendance
     * @return array
     */
    private function generateBreaksForAttendance($attendance): array
    {
        $breaks = [];

        // 勤怠が終了していない場合は休憩データを作成しない
        if ($attendance->status !== 'finished' || !$attendance->start_time || !$attendance->end_time) {
            return $breaks;
        }

        $startTime = \Carbon\Carbon::createFromTimeString($attendance->start_time);
        $endTime = \Carbon\Carbon::createFromTimeString($attendance->end_time);
        $workDuration = $endTime->diffInHours($startTime);

        // 勤務時間が6時間未満の場合は休憩なし
        if ($workDuration < 6) {
            return $breaks;
        }

        // 6時間以上8時間未満の場合は1時間休憩
        if ($workDuration < 8) {
            $breakStart = $startTime->copy()->addHours(4); // 4時間後に休憩開始
            $breakEnd = $breakStart->copy()->addHour(); // 1時間休憩

            $breaks[] = [
                'id' => Str::uuid(),
                'attendance_id' => $attendance->id,
                'start_time' => $breakStart->format('H:i:s'),
                'end_time' => $breakEnd->format('H:i:s'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        } else {
            // 8時間以上の場合は2時間休憩（昼休み + 午後休憩）

            // 昼休み（12:00-13:00）
            $lunchStart = \Carbon\Carbon::createFromTimeString('12:00:00');
            $lunchEnd = \Carbon\Carbon::createFromTimeString('13:00:00');

            $breaks[] = [
                'id' => Str::uuid(),
                'attendance_id' => $attendance->id,
                'start_time' => $lunchStart->format('H:i:s'),
                'end_time' => $lunchEnd->format('H:i:s'),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // 午後休憩（15:00-15:15）
            $afternoonStart = \Carbon\Carbon::createFromTimeString('15:00:00');
            $afternoonEnd = \Carbon\Carbon::createFromTimeString('15:15:00');

            $breaks[] = [
                'id' => Str::uuid(),
                'attendance_id' => $attendance->id,
                'start_time' => $afternoonStart->format('H:i:s'),
                'end_time' => $afternoonEnd->format('H:i:s'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $breaks;
    }
}
