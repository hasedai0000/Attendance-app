<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ModificationRequestBreakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 既存の修正申請IDを取得
        $modificationRequestIds = DB::table('modification_requests')->pluck('id')->toArray();

        if (empty($modificationRequestIds)) {
            $this->command->error('No modification requests found. Please run ModificationRequestSeeder first.');
            return;
        }

        $this->command->info('修正申請休憩データを作成中...');

        // テスト用の修正申請休憩データを作成
        $modificationRequestBreaks = [
            [
                'id' => Str::uuid(),
                'modification_request_id' => $modificationRequestIds[0], // 最初の修正申請のID
                'requested_start_time' => '12:15:00',
                'requested_end_time' => '13:15:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'modification_request_id' => $modificationRequestIds[0], // 同じ修正申請に複数の休憩
                'requested_start_time' => '15:30:00',
                'requested_end_time' => '15:45:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // 2番目の修正申請が存在する場合のみ追加
        if (isset($modificationRequestIds[1])) {
            $modificationRequestBreaks[] = [
                'id' => Str::uuid(),
                'modification_request_id' => $modificationRequestIds[1], // 2番目の修正申請のID
                'requested_start_time' => '12:00:00',
                'requested_end_time' => '13:00:00',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ];
        }

        // 3番目の修正申請が存在する場合のみ追加
        if (isset($modificationRequestIds[2])) {
            $modificationRequestBreaks[] = [
                'id' => Str::uuid(),
                'modification_request_id' => $modificationRequestIds[2], // 3番目の修正申請のID
                'requested_start_time' => '11:30:00',
                'requested_end_time' => '12:30:00',
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ];
        }

        try {
            DB::table('modification_request_breaks')->insert($modificationRequestBreaks);
            $this->command->info('修正申請休憩データを ' . count($modificationRequestBreaks) . ' 件作成しました。');
        } catch (\Exception $e) {
            $this->command->error('修正申請休憩データの作成に失敗しました: ' . $e->getMessage());
        }
    }
}
