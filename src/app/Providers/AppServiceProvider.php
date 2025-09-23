<?php

namespace App\Providers;

use App\Application\Contracts\FileUploadServiceInterface;
use App\Application\Services\FileUploadService;
use App\Domain\Attendance\Repositories\AttendanceRepositoryInterface;
use App\Domain\Breaks\Repositories\BreaksRepositoryInterface;
use App\Domain\Item\Repositories\CategoryRepositoryInterface;
use App\Domain\Item\Repositories\CommentRepositoryInterface;
use App\Domain\Item\Repositories\ItemCategoryRepositoryInterface;
use App\Domain\Item\Repositories\ItemRepositoryInterface;
use App\Domain\Item\Repositories\LikeRepositoryInterface;
use App\Domain\ModificationRequest\Repositories\ModificationRequestInterface;
use App\Domain\ModificationRequestBreaks\Repositories\ModificationRequestBreaksInterface;
use App\Domain\Profile\Repositories\ProfileRepositoryInterface;
use App\Domain\Purchase\Repositories\PurchaseRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Repositories\EloquentAttendanceRepository;
use App\Infrastructure\Repositories\EloquentBreaksRepository;
use App\Infrastructure\Repositories\EloquentCategoryRepository;
use App\Infrastructure\Repositories\EloquentCommentRepository;
use App\Infrastructure\Repositories\EloquentItemCategoryRepository;
use App\Infrastructure\Repositories\EloquentItemRepository;
use App\Infrastructure\Repositories\EloquentLikeRepository;
use App\Infrastructure\Repositories\EloquentModificationRequestRepository;
use App\Infrastructure\Repositories\EloquentModificationRequestBreaksRepository;
use App\Infrastructure\Repositories\EloquentProfileRepository;
use App\Infrastructure\Repositories\EloquentPurchaseRepository;
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
        
        // アイテム関連のリポジトリ
        $this->app->bind(ItemRepositoryInterface::class, EloquentItemRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(ItemCategoryRepositoryInterface::class, EloquentItemCategoryRepository::class);
        $this->app->bind(CommentRepositoryInterface::class, EloquentCommentRepository::class);
        $this->app->bind(LikeRepositoryInterface::class, EloquentLikeRepository::class);
        
        // プロフィール関連のリポジトリ
        $this->app->bind(ProfileRepositoryInterface::class, EloquentProfileRepository::class);
        
        // 購入関連のリポジトリ
        $this->app->bind(PurchaseRepositoryInterface::class, EloquentPurchaseRepository::class);
        
        // サービス
        $this->app->bind(FileUploadServiceInterface::class, FileUploadService::class);
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
