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
        // 既存のユーザーIDを取得
        $users = DB::table('users')->pluck('id')->toArray();

        if (empty($users)) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        // テスト用の勤怠データを作成
        $attendances = [
            [
                'id' => Str::uuid(),
                'user_id' => $users[0], // 最初のユーザー
                'date' => '2024-09-20',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'status' => 'finished',
                'remarks' => '通常勤務',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'user_id' => $users[0], // 同じユーザー
                'date' => '2024-09-21',
                'start_time' => '09:15:00',
                'end_time' => '17:45:00',
                'status' => 'finished',
                'remarks' => '少し遅刻',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'user_id' => isset($users[1]) ? $users[1] : $users[0], // 2番目のユーザー（存在しない場合は最初のユーザー）
                'date' => '2024-09-20',
                'start_time' => '08:30:00',
                'end_time' => '17:30:00',
                'status' => 'finished',
                'remarks' => '早出勤務',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('attendances')->insert($attendances);
    }
}
