<?php

namespace App\Providers;

use App\Application\Contracts\FileUploadServiceInterface;
use App\Application\Services\FileUploadService;
use App\Domain\Attendance\Repositories\AttendanceRepositoryInterface;
use App\Domain\Breaks\Repositories\BreaksRepositoryInterface;
use App\Domain\ModificationRequest\Repositories\ModificationRequestInterface;
use App\Domain\ModificationRequestBreaks\Repositories\ModificationRequestBreaksInterface;
// ProfileとPurchase関連のリポジトリは出退勤管理アプリケーションでは使用しないため、コメントアウト
// use App\Domain\Profile\Repositories\ProfileRepositoryInterface;
// use App\Domain\Purchase\Repositories\PurchaseRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Repositories\EloquentAttendanceRepository;
use App\Infrastructure\Repositories\EloquentBreaksRepository;
use App\Infrastructure\Repositories\EloquentModificationRequestRepository;
use App\Infrastructure\Repositories\EloquentModificationRequestBreaksRepository;
// ProfileとPurchase関連のEloquentリポジトリは出退勤管理アプリケーションでは使用しないため、コメントアウト
// use App\Infrastructure\Repositories\EloquentProfileRepository;
// use App\Infrastructure\Repositories\EloquentPurchaseRepository;
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
