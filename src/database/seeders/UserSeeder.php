<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('ユーザーシーダーを開始します...');

        // トランザクション内で実行
        DB::transaction(function () {
            $users = $this->getUserData();
            $createdCount = 0;
            $skippedCount = 0;

            foreach ($users as $userData) {
                $result = $this->createUserIfNotExists($userData);
                if ($result) {
                    $createdCount++;
                } else {
                    $skippedCount++;
                }
            }

            $this->command->info("ユーザー作成完了: 新規作成 {$createdCount}件, スキップ {$skippedCount}件");
        });

        // 最終的なユーザー数を表示
        $totalUsers = User::count();
        $this->command->info("データベース内の総ユーザー数: {$totalUsers}件");
    }

    /**
     * ユーザーデータを取得
     *
     * @return array
     */
    private function getUserData(): array
    {
        return [
            // 管理者ユーザー
            [
                'name' => '管理者',
                'email' => 'admin@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_admin' => true,
            ],
            [
                'name' => 'テストユーザー',
                'email' => 'test@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_admin' => true,
            ],

            // 一般ユーザー（メール認証済み）
            [
                'name' => '田中太郎',
                'email' => 'tanaka@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_admin' => false,
            ],
            [
                'name' => '佐藤花子',
                'email' => 'sato@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_admin' => false,
            ],
            [
                'name' => '鈴木美咲',
                'email' => 'suzuki@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_admin' => false,
            ],
            [
                'name' => '高橋健太',
                'email' => 'takahashi@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_admin' => false,
            ],
            [
                'name' => '渡辺翔太',
                'email' => 'watanabe@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_admin' => false,
            ],
            [
                'name' => '中村優子',
                'email' => 'nakamura@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_admin' => false,
            ],
            [
                'name' => '小林大輔',
                'email' => 'kobayashi@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_admin' => false,
            ],
            [
                'name' => '加藤結衣',
                'email' => 'kato@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_admin' => false,
            ],
            [
                'name' => '森田浩司',
                'email' => 'morita@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_admin' => false,
            ],
            [
                'name' => '松本あかり',
                'email' => 'matsumoto@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_admin' => false,
            ],
            [
                'name' => '橋本拓也',
                'email' => 'hashimoto@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_admin' => false,
            ],
            [
                'name' => '清水恵子',
                'email' => 'shimizu@example.com',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_admin' => false,
            ],

            // メール未認証ユーザー
            [
                'name' => '山田次郎',
                'email' => 'yamada@example.com',
                'password' => 'password',
                'email_verified_at' => null,
                'is_admin' => false,
            ],
            [
                'name' => '伊藤愛',
                'email' => 'ito@example.com',
                'password' => 'password',
                'email_verified_at' => null,
                'is_admin' => false,
            ],
        ];
    }

    /**
     * ユーザーが存在しない場合のみ作成
     *
     * @param array $userData
     * @return bool 作成された場合はtrue、スキップされた場合はfalse
     */
    private function createUserIfNotExists(array $userData): bool
    {
        $existingUser = User::where('email', $userData['email'])->first();

        if ($existingUser) {
            $this->command->warn("ユーザー '{$userData['email']}' は既に存在します。スキップします。");
            return false;
        }

        try {
            // User::create()ではis_adminが保存されないため、直接DB::table()を使用
            DB::table('users')->insert([
                'id' => Str::uuid(),
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'email_verified_at' => $userData['email_verified_at'],
                'is_admin' => $userData['is_admin'] ?? false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info("ユーザー '{$userData['name']}' ({$userData['email']}) を作成しました。");
            return true;
        } catch (\Exception $e) {
            $this->command->error("ユーザー '{$userData['email']}' の作成に失敗しました: " . $e->getMessage());
            return false;
        }
    }
}
