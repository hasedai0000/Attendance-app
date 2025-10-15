<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TestScenarioSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $this->command->info('テストシナリオ用のデータを作成中...');

    // 既存のユーザーIDを取得
    $users = DB::table('users')->pluck('id')->toArray();

    if (empty($users)) {
      $this->command->error('ユーザーが見つかりません。UserSeederを先に実行してください。');
      return;
    }

    $createdCount = 0;

    // テストシナリオ用の勤怠データを作成
    $testScenarios = $this->generateTestScenarios($users);

    foreach ($testScenarios as $scenario) {
      try {
        // 既存の勤怠データをチェック
        $existingAttendance = DB::table('attendances')
          ->where('user_id', $scenario['attendance']['user_id'])
          ->where('date', $scenario['attendance']['date'])
          ->first();

        if ($existingAttendance) {
          $this->command->warn("テストシナリオ '{$scenario['name']}' は既に存在するためスキップしました。");
          continue;
        }

        DB::table('attendances')->insert($scenario['attendance']);
        $createdCount++;

        // 休憩データがある場合は作成
        if (!empty($scenario['breaks'])) {
          DB::table('breaks')->insert($scenario['breaks']);
        }

        // 修正申請データがある場合は作成
        if (!empty($scenario['modification_request'])) {
          DB::table('modification_requests')->insert($scenario['modification_request']);
        }

        $this->command->info("テストシナリオ '{$scenario['name']}' を作成しました。");
      } catch (\Exception $e) {
        $this->command->error("テストシナリオ '{$scenario['name']}' の作成に失敗しました: " . $e->getMessage());
      }
    }

    $this->command->info("テストシナリオデータ作成完了: 合計 {$createdCount} 件作成しました。");
  }

  /**
   * テストシナリオ用のデータを生成
   *
   * @param array $users
   * @return array
   */
  private function generateTestScenarios(array $users): array
  {
    $scenarios = [];
    $today = Carbon::today();
    $yesterday = $today->copy()->subDay();
    $lastWeek = $today->copy()->subWeek();

    // ユーザーインデックスを管理して重複を避ける
    $userIndex = 0;
    $getNextUser = function () use (&$userIndex, $users) {
      $user = $users[$userIndex % count($users)];
      $userIndex++;
      return $user;
    };

    // シナリオ1: 今日の勤務中ユーザー
    $scenarios[] = $this->createWorkingUserScenario($getNextUser(), $today);

    // シナリオ2: 今日の休憩中ユーザー
    $scenarios[] = $this->createOnBreakUserScenario($getNextUser(), $today);

    // シナリオ3: 今日の退勤済みユーザー
    $scenarios[] = $this->createFinishedUserScenario($getNextUser(), $today);

    // シナリオ4: 昨日の勤怠データ（修正申請あり）
    $scenarios[] = $this->createModificationRequestScenario($getNextUser(), $yesterday);

    // シナリオ5: 先週の勤怠データ（複数日分）
    for ($i = 0; $i < 3; $i++) {
      $date = $lastWeek->copy()->addDays($i);
      if (!$date->isWeekend()) {
        $scenarios[] = $this->createHistoricalAttendanceScenario($getNextUser(), $date, $i);
      }
    }

    // シナリオ6: 管理者ユーザーの勤怠データ
    $adminUser = DB::table('users')->where('is_admin', true)->first();
    if ($adminUser) {
      $scenarios[] = $this->createAdminUserScenario($adminUser->id, $today);
    }

    // シナリオ7: エッジケース - 深夜勤務（昨日の日付を使用）
    $scenarios[] = $this->createNightShiftScenario($getNextUser(), $yesterday);

    // シナリオ8: エッジケース - 短時間勤務（一昨日の日付を使用）
    $dayBeforeYesterday = $today->copy()->subDays(2);
    $scenarios[] = $this->createShortWorkScenario($getNextUser(), $dayBeforeYesterday);

    return $scenarios;
  }

  /**
   * 勤務中ユーザーのシナリオを作成
   */
  private function createWorkingUserScenario(string $userId, Carbon $date): array
  {
    $attendanceId = Str::uuid();

    return [
      'name' => '勤務中ユーザー',
      'attendance' => [
        'id' => $attendanceId,
        'user_id' => $userId,
        'date' => $date->format('Y-m-d'),
        'start_time' => '09:00:00',
        'end_time' => null,
        'status' => 'working',
        'remarks' => '勤務中',
        'created_at' => $date->copy()->setTime(9, 0),
        'updated_at' => now(),
      ],
      'breaks' => [],
      'modification_request' => [],
    ];
  }

  /**
   * 休憩中ユーザーのシナリオを作成
   */
  private function createOnBreakUserScenario(string $userId, Carbon $date): array
  {
    $attendanceId = Str::uuid();

    return [
      'name' => '休憩中ユーザー',
      'attendance' => [
        'id' => $attendanceId,
        'user_id' => $userId,
        'date' => $date->format('Y-m-d'),
        'start_time' => '09:00:00',
        'end_time' => null,
        'status' => 'on_break',
        'remarks' => '休憩中',
        'created_at' => $date->copy()->setTime(9, 0),
        'updated_at' => now(),
      ],
      'breaks' => [
        [
          'id' => Str::uuid(),
          'attendance_id' => $attendanceId,
          'start_time' => '12:00:00',
          'end_time' => null, // 休憩中なので終了時間なし
          'created_at' => $date->copy()->setTime(12, 0),
          'updated_at' => now(),
        ],
      ],
      'modification_request' => [],
    ];
  }

  /**
   * 退勤済みユーザーのシナリオを作成
   */
  private function createFinishedUserScenario(string $userId, Carbon $date): array
  {
    $attendanceId = Str::uuid();

    return [
      'name' => '退勤済みユーザー',
      'attendance' => [
        'id' => $attendanceId,
        'user_id' => $userId,
        'date' => $date->format('Y-m-d'),
        'start_time' => '09:00:00',
        'end_time' => '18:00:00',
        'status' => 'finished',
        'remarks' => '通常勤務',
        'created_at' => $date->copy()->setTime(9, 0),
        'updated_at' => $date->copy()->setTime(18, 0),
      ],
      'breaks' => [
        [
          'id' => Str::uuid(),
          'attendance_id' => $attendanceId,
          'start_time' => '12:00:00',
          'end_time' => '13:00:00',
          'created_at' => $date->copy()->setTime(12, 0),
          'updated_at' => $date->copy()->setTime(13, 0),
        ],
        [
          'id' => Str::uuid(),
          'attendance_id' => $attendanceId,
          'start_time' => '15:00:00',
          'end_time' => '15:15:00',
          'created_at' => $date->copy()->setTime(15, 0),
          'updated_at' => $date->copy()->setTime(15, 15),
        ],
      ],
      'modification_request' => [],
    ];
  }

  /**
   * 修正申請ありのシナリオを作成
   */
  private function createModificationRequestScenario(string $userId, Carbon $date): array
  {
    $attendanceId = Str::uuid();
    $modificationRequestId = Str::uuid();
    $adminUser = DB::table('users')->where('is_admin', true)->first();

    return [
      'name' => '修正申請ありユーザー',
      'attendance' => [
        'id' => $attendanceId,
        'user_id' => $userId,
        'date' => $date->format('Y-m-d'),
        'start_time' => '09:15:00',
        'end_time' => '18:15:00',
        'status' => 'finished',
        'remarks' => '遅刻・残業',
        'created_at' => $date->copy()->setTime(9, 15),
        'updated_at' => $date->copy()->setTime(18, 15),
      ],
      'breaks' => [
        [
          'id' => Str::uuid(),
          'attendance_id' => $attendanceId,
          'start_time' => '12:00:00',
          'end_time' => '13:00:00',
          'created_at' => $date->copy()->setTime(12, 0),
          'updated_at' => $date->copy()->setTime(13, 0),
        ],
      ],
      'modification_request' => [
        [
          'id' => $modificationRequestId,
          'attendance_id' => $attendanceId,
          'user_id' => $userId,
          'requested_start_time' => '09:00:00',
          'requested_end_time' => '18:00:00',
          'requested_remarks' => '電車の遅延により出勤時刻を修正したいです',
          'status' => 'pending',
          'approved_by' => null,
          'approved_at' => null,
          'created_at' => $date->copy()->setTime(9, 30),
          'updated_at' => $date->copy()->setTime(9, 30),
        ],
      ],
    ];
  }

  /**
   * 過去の勤怠データのシナリオを作成
   */
  private function createHistoricalAttendanceScenario(string $userId, Carbon $date, int $dayIndex): array
  {
    $attendanceId = Str::uuid();
    $patterns = [
      ['start' => '08:30:00', 'end' => '17:30:00', 'remarks' => '早出勤務'],
      ['start' => '09:00:00', 'end' => '18:00:00', 'remarks' => '通常勤務'],
      ['start' => '09:30:00', 'end' => '19:00:00', 'remarks' => '遅刻・残業'],
    ];

    $pattern = $patterns[$dayIndex % count($patterns)];

    return [
      'name' => "過去勤怠データ（" . ($dayIndex + 1) . "日目）",
      'attendance' => [
        'id' => $attendanceId,
        'user_id' => $userId,
        'date' => $date->format('Y-m-d'),
        'start_time' => $pattern['start'],
        'end_time' => $pattern['end'],
        'status' => 'finished',
        'remarks' => $pattern['remarks'],
        'created_at' => $date->copy()->setTimeFromTimeString($pattern['start']),
        'updated_at' => $date->copy()->setTimeFromTimeString($pattern['end']),
      ],
      'breaks' => [
        [
          'id' => Str::uuid(),
          'attendance_id' => $attendanceId,
          'start_time' => '12:00:00',
          'end_time' => '13:00:00',
          'created_at' => $date->copy()->setTime(12, 0),
          'updated_at' => $date->copy()->setTime(13, 0),
        ],
      ],
      'modification_request' => [],
    ];
  }

  /**
   * 管理者ユーザーのシナリオを作成
   */
  private function createAdminUserScenario(string $userId, Carbon $date): array
  {
    $attendanceId = Str::uuid();

    return [
      'name' => '管理者ユーザー',
      'attendance' => [
        'id' => $attendanceId,
        'user_id' => $userId,
        'date' => $date->format('Y-m-d'),
        'start_time' => '08:00:00',
        'end_time' => '20:00:00',
        'status' => 'finished',
        'remarks' => '管理者勤務',
        'created_at' => $date->copy()->setTime(8, 0),
        'updated_at' => $date->copy()->setTime(20, 0),
      ],
      'breaks' => [
        [
          'id' => Str::uuid(),
          'attendance_id' => $attendanceId,
          'start_time' => '12:00:00',
          'end_time' => '13:00:00',
          'created_at' => $date->copy()->setTime(12, 0),
          'updated_at' => $date->copy()->setTime(13, 0),
        ],
        [
          'id' => Str::uuid(),
          'attendance_id' => $attendanceId,
          'start_time' => '15:00:00',
          'end_time' => '15:15:00',
          'created_at' => $date->copy()->setTime(15, 0),
          'updated_at' => $date->copy()->setTime(15, 15),
        ],
        [
          'id' => Str::uuid(),
          'attendance_id' => $attendanceId,
          'start_time' => '17:00:00',
          'end_time' => '17:15:00',
          'created_at' => $date->copy()->setTime(17, 0),
          'updated_at' => $date->copy()->setTime(17, 15),
        ],
      ],
      'modification_request' => [],
    ];
  }

  /**
   * 深夜勤務のシナリオを作成
   */
  private function createNightShiftScenario(string $userId, Carbon $date): array
  {
    $attendanceId = Str::uuid();

    return [
      'name' => '深夜勤務ユーザー',
      'attendance' => [
        'id' => $attendanceId,
        'user_id' => $userId,
        'date' => $date->format('Y-m-d'),
        'start_time' => '22:00:00',
        'end_time' => '06:00:00',
        'status' => 'finished',
        'remarks' => '深夜勤務',
        'created_at' => $date->copy()->setTime(22, 0),
        'updated_at' => $date->copy()->addDay()->setTime(6, 0),
      ],
      'breaks' => [
        [
          'id' => Str::uuid(),
          'attendance_id' => $attendanceId,
          'start_time' => '01:00:00',
          'end_time' => '02:00:00',
          'created_at' => $date->copy()->addDay()->setTime(1, 0),
          'updated_at' => $date->copy()->addDay()->setTime(2, 0),
        ],
      ],
      'modification_request' => [],
    ];
  }

  /**
   * 短時間勤務のシナリオを作成
   */
  private function createShortWorkScenario(string $userId, Carbon $date): array
  {
    $attendanceId = Str::uuid();

    return [
      'name' => '短時間勤務ユーザー',
      'attendance' => [
        'id' => $attendanceId,
        'user_id' => $userId,
        'date' => $date->format('Y-m-d'),
        'start_time' => '10:00:00',
        'end_time' => '14:00:00',
        'status' => 'finished',
        'remarks' => '短時間勤務',
        'created_at' => $date->copy()->setTime(10, 0),
        'updated_at' => $date->copy()->setTime(14, 0),
      ],
      'breaks' => [], // 4時間未満なので休憩なし
      'modification_request' => [],
    ];
  }
}
