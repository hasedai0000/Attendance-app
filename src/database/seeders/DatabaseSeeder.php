<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Added this import for DB facade
use Illuminate\Support\Str; // Added this import for Str facade

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('=== 勤怠管理システム データベースシーダー開始 ===');
        $startTime = microtime(true);

        try {
            // \App\Models\User::factory(10)->create();

            $seeders = [
                UserSeeder::class,           // 1. ユーザーを最初に作成
                // 勤怠管理システムのシーダー
                AttendanceSeeder::class,                    // 2. 勤怠記録を作成
                BreakSeeder::class,                         // 3. 休憩記録を作成
                ModificationRequestSeeder::class,           // 4. 修正申請を作成
                ModificationRequestBreakSeeder::class,      // 5. 修正申請休憩を作成
                TestScenarioSeeder::class,                  // 6. テストシナリオ用データを作成
            ];

            $successCount = 0;
            $errorCount = 0;

            foreach ($seeders as $index => $seederClass) {
                $seederName = class_basename($seederClass);
                $this->command->info("(" . ($index + 1) . "/" . count($seeders) . ") {$seederName} を実行中...");

                try {
                    $seederStartTime = microtime(true);
                    $this->call($seederClass);
                    $seederEndTime = microtime(true);
                    $seederDuration = round($seederEndTime - $seederStartTime, 2);

                    $this->command->info("✓ {$seederName} 完了 ({$seederDuration}秒)");
                    $successCount++;
                } catch (\Exception $e) {
                    $this->command->error("✗ {$seederName} 失敗: " . $e->getMessage());
                    $this->command->error("スタックトレース: " . $e->getTraceAsString());
                    $errorCount++;

                    // 重要なSeederでエラーが発生した場合は処理を停止
                    if (in_array($seederClass, [UserSeeder::class, AttendanceSeeder::class])) {
                        throw $e;
                    }
                }
            }

            $endTime = microtime(true);
            $totalDuration = round($endTime - $startTime, 2);

            $this->command->info('=== シーダー実行結果 ===');
            $this->command->info("成功: {$successCount}件");
            if ($errorCount > 0) {
                $this->command->warn("失敗: {$errorCount}件");
            }
            $this->command->info("実行時間: {$totalDuration}秒");
            $this->command->info('=== 勤怠管理システム データベースシーダー完了 ===');

            if ($errorCount === 0) {
                $this->command->info('🎉 すべてのシーダーが正常に実行されました！');
                $this->command->info('勤怠管理システムの包括的なテストデータが準備されました。');
            } else {
                $this->command->warn("⚠️  {$errorCount}件のシーダーでエラーが発生しました。");
            }
        } catch (\Exception $e) {
            $this->command->error('=== シーダー実行中に致命的なエラーが発生しました ===');
            $this->command->error('エラー: ' . $e->getMessage());
            $this->command->error('ファイル: ' . $e->getFile() . ':' . $e->getLine());
            $this->command->error('スタックトレース:');
            $this->command->error($e->getTraceAsString());

            throw $e;
        }
    }
}
