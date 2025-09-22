<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ModificationRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('修正申請データを作成中...');

        // 既存のattendance_idとuser_idを取得
        $attendances = DB::table('attendances')->pluck('id')->toArray();
        $users = DB::table('users')->pluck('id')->toArray();

        if (empty($attendances) || empty($users)) {
            $this->command->error('No attendances or users found. Please run AttendanceSeeder and UserSeeder first.');
            return;
        }

        // 管理者ユーザーを取得（is_adminカラムが存在する場合のみ）
        $adminUserId = $users[0]; // デフォルトは最初のユーザー
        if (DB::getSchemaBuilder()->hasColumn('users', 'is_admin')) {
            $adminUser = DB::table('users')->where('is_admin', true)->first();
            if ($adminUser) {
                $adminUserId = $adminUser->id;
            }
        }

        // テスト用の修正申請データを作成（3つの修正申請を作成）
        $modificationRequests = [
            [
                'id' => Str::uuid(),
                'attendance_id' => $attendances[0],
                'user_id' => $users[0],
                'requested_start_time' => '08:45:00',
                'requested_end_time' => '17:45:00',
                'requested_remarks' => '電車の遅延により出勤時刻を修正したいです',
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'attendance_id' => isset($attendances[1]) ? $attendances[1] : $attendances[0],
                'user_id' => isset($users[1]) ? $users[1] : $users[0],
                'requested_start_time' => '09:00:00',
                'requested_end_time' => '18:15:00',
                'requested_remarks' => '残業時間の修正申請です',
                'status' => 'approved',
                'approved_by' => $adminUserId,
                'approved_at' => now()->subHours(2),
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subHours(2),
            ],
            [
                'id' => Str::uuid(),
                'attendance_id' => isset($attendances[2]) ? $attendances[2] : $attendances[0],
                'user_id' => isset($users[2]) ? $users[2] : $users[0],
                'requested_start_time' => '09:15:00',
                'requested_end_time' => '18:30:00',
                'requested_remarks' => '会議のため退勤時刻を延長したいです',
                'status' => 'pending', // 'rejected'から'pending'に変更
                'approved_by' => null, // 未承認なのでnull
                'approved_at' => null, // 未承認なのでnull
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ],
        ];

        try {
            DB::table('modification_requests')->insert($modificationRequests);
            $this->command->info('修正申請データを ' . count($modificationRequests) . ' 件作成しました。');
        } catch (\Exception $e) {
            $this->command->error('修正申請データの作成に失敗しました: ' . $e->getMessage());
        }
    }
}
