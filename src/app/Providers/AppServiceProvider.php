<?php

namespace App\Providers;

use App\Domain\Attendance\Repositories\AttendanceRepositoryInterface;
use App\Domain\Breaks\Repositories\BreaksRepositoryInterface;
use App\Domain\ModificationRequest\Repositories\ModificationRequestInterface;
use App\Domain\ModificationRequestBreaks\Repositories\ModificationRequestBreaksInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Repositories\EloquentAttendanceRepository;
use App\Infrastructure\Repositories\EloquentBreaksRepository;
use App\Infrastructure\Repositories\EloquentModificationRequestRepository;
use App\Infrastructure\Repositories\EloquentModificationRequestBreaksRepository;
use App\Infrastructure\Repositories\EloquentUserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 勤怠管理関連のリポジトリ
        $this->app->bind(AttendanceRepositoryInterface::class, EloquentAttendanceRepository::class);
        $this->app->bind(BreaksRepositoryInterface::class, EloquentBreaksRepository::class);
        $this->app->bind(ModificationRequestInterface::class, EloquentModificationRequestRepository::class);
        $this->app->bind(ModificationRequestBreaksInterface::class, EloquentModificationRequestBreaksRepository::class);
        // ユーザー関連のリポジトリ
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 開発環境でのみSQLログを有効化
        if (config('app.debug')) {
            DB::listen(function ($query) {
                Log::info(
                    $query->sql,
                    [
                        'bindings' => $query->bindings,
                        'time' => $query->time,
                    ]
                );
            });
        }
    }
}
