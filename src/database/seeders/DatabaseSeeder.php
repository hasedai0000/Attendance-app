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
        // \App\Models\User::factory(10)->create();

        $this->call([
            UserSeeder::class,           // 1. ユーザーを最初に作成
            // 勤怠管理システムのシーダー
            AttendanceSeeder::class,                    // 8. 勤怠記録を作成
            BreakSeeder::class,                         // 9. 休憩記録を作成
            ModificationRequestSeeder::class,           // 10. 修正申請を作成
            ModificationRequestBreakSeeder::class,      // 11. 修正申請休憩を作成
        ]);

        $this->command->info('All seeders have been executed successfully!');
        $this->command->info('Your application now has comprehensive test data including attendance management.');
    }
}
