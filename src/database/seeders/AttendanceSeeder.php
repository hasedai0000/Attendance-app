<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('勤怠データを作成中...');

        // 既存のユーザーIDを取得
        $users = DB::table('users')->pluck('id')->toArray();

        if (empty($users)) {
            $this->command->error('ユーザーが見つかりません。UserSeederを先に実行してください。');
            return;
        }

        $createdCount = 0;

        // 各ユーザーに対して多様な勤怠パターンを作成
        foreach ($users as $index => $userId) {
            $userAttendances = $this->generateAttendancesForUser($userId, $index);

            if (!empty($userAttendances)) {
                try {
                    DB::table('attendances')->insert($userAttendances);
                    $createdCount += count($userAttendances);
                    $this->command->info("ユーザーID {$userId} に勤怠データを " . count($userAttendances) . " 件作成しました。");
                } catch (\Exception $e) {
                    $this->command->error("ユーザーID {$userId} の勤怠データ作成に失敗しました: " . $e->getMessage());
                }
            }
        }

        $this->command->info("勤怠データ作成完了: 合計 {$createdCount} 件作成しました。");
    }

    /**
     * ユーザーごとの勤怠データを生成
     *
     * @param string $userId
     * @param int $userIndex
     * @return array
     */
    private function generateAttendancesForUser(string $userId, int $userIndex): array
    {
        $attendances = [];
        $baseDate = now()->subDays(30); // 30日前から開始

        // ユーザーごとに異なる勤怠パターンを設定
        $patterns = [
            'regular',      // 通常勤務
            'early',        // 早出勤務
            'late',         // 遅刻勤務
            'overtime',     // 残業勤務
            'part_time',    // 短時間勤務
            'irregular',    // 不規則勤務
        ];

        $pattern = $patterns[$userIndex % count($patterns)];

        // 過去30日分の勤怠データを作成（土日は除く）
        for ($i = 0; $i < 30; $i++) {
            $date = $baseDate->copy()->addDays($i);

            // 土日はスキップ
            if ($date->isWeekend()) {
                continue;
            }

            $attendance = $this->generateAttendanceForDate($userId, $date, $pattern, $i);
            if ($attendance) {
                $attendances[] = $attendance;
            }
        }

        return $attendances;
    }

    /**
     * 特定の日付の勤怠データを生成
     *
     * @param string $userId
     * @param \Carbon\Carbon $date
     * @param string $pattern
     * @param int $dayIndex
     * @return array|null
     */
    private function generateAttendanceForDate(string $userId, \Carbon\Carbon $date, string $pattern, int $dayIndex): ?array
    {
        // 10%の確率で欠勤
        if (rand(1, 100) <= 10) {
            return null;
        }

        $attendance = [
            'id' => Str::uuid(),
            'user_id' => $userId,
            'date' => $date->format('Y-m-d'),
            'created_at' => $date->copy()->addHours(rand(8, 10)),
            'updated_at' => $date->copy()->addHours(rand(17, 19)),
        ];

        switch ($pattern) {
            case 'regular':
                $attendance['start_time'] = '09:00:00';
                $attendance['end_time'] = '18:00:00';
                $attendance['status'] = 'finished';
                $attendance['remarks'] = '通常勤務';
                break;

            case 'early':
                $attendance['start_time'] = '08:00:00';
                $attendance['end_time'] = '17:00:00';
                $attendance['status'] = 'finished';
                $attendance['remarks'] = '早出勤務';
                break;

            case 'late':
                $attendance['start_time'] = '10:00:00';
                $attendance['end_time'] = '19:00:00';
                $attendance['status'] = 'finished';
                $attendance['remarks'] = '遅刻勤務';
                break;

            case 'overtime':
                $attendance['start_time'] = '09:00:00';
                $attendance['end_time'] = '21:00:00';
                $attendance['status'] = 'finished';
                $attendance['remarks'] = '残業勤務';
                break;

            case 'part_time':
                $attendance['start_time'] = '10:00:00';
                $attendance['end_time'] = '15:00:00';
                $attendance['status'] = 'finished';
                $attendance['remarks'] = '短時間勤務';
                break;

            case 'irregular':
                $startHour = rand(8, 11);
                $endHour = rand(17, 22);
                $attendance['start_time'] = sprintf('%02d:%02d:00', $startHour, rand(0, 59));
                $attendance['end_time'] = sprintf('%02d:%02d:00', $endHour, rand(0, 59));
                $attendance['status'] = 'finished';
                $attendance['remarks'] = '不規則勤務';
                break;
        }

        // 5%の確率で勤務中状態
        if (rand(1, 100) <= 5) {
            $attendance['status'] = 'working';
            $attendance['end_time'] = null;
            $attendance['remarks'] = '勤務中';
        }

        // 2%の確率で休憩中状態
        if (rand(1, 100) <= 2) {
            $attendance['status'] = 'on_break';
            $attendance['end_time'] = null;
            $attendance['remarks'] = '休憩中';
        }

        return $attendance;
    }
}
