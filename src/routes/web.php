<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ModificationRequestController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 認証関連ルート（ゲスト用）
Route::middleware('guest')->group(function () {
    // PG02 ログイン画面（一般ユーザー）
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    // PG01 会員登録画面（一般ユーザー）
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    // PG07 ログイン画面（管理者）
    Route::get('/admin/login', [AuthController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'adminLogin']);
});

// 認証関連ルート（認証済みユーザー用）
Route::middleware('auth')->group(function () {
    // ログアウト
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    // メール認証
    Route::get('/email/verify', [AuthController::class, 'showVerificationNotice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerificationEmail'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// 認証済みユーザー向けルート
Route::middleware(['auth', 'verified'])->group(function () {
    // PG03 勤怠登録画面（一般ユーザー）
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    // 勤怠打刻機能
    Route::post('/attendance/start-work', [AttendanceController::class, 'startWork'])->name('attendance.start-work');
    Route::post('/attendance/start-break', [AttendanceController::class, 'startBreak'])->name('attendance.start-break');
    Route::post('/attendance/end-break', [AttendanceController::class, 'endBreak'])->name('attendance.end-break');
    Route::post('/attendance/end-work', [AttendanceController::class, 'endWork'])->name('attendance.end-work');
    // PG04 勤怠一覧画面（一般ユーザー）
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    // PG05 勤怠詳細画面（一般ユーザー）
    // PG09 勤怠詳細画面（管理者）
    Route::get('/attendance/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    // PG06 申請一覧画面（一般ユーザー）
    // PG12 申請一覧画面（管理者） 
    Route::get('/stamp_correction_request/list', [ModificationRequestController::class, 'index'])->name('modification-requests.index');
    Route::post('/stamp_correction_request/list', [ModificationRequestController::class, 'store'])->name('modification-requests.store');
    // 申請詳細画面（一般ユーザー）
    Route::get('/stamp_correction_request/{id}', [ModificationRequestController::class, 'show'])->name('modification-requests.show');
});

// 管理者専用ルート
Route::middleware(['auth', 'admin'])->group(function () {
    // PG08 勤怠一覧画面（管理者）
    Route::get('/admin/attendance/list', [AdminController::class, 'dailyAttendance'])->name('admin.attendance.daily');
    // PG10 スタッフ一覧画面（管理者）
    Route::get('/admin/staff/list', [AdminController::class, 'staffList'])->name('admin.staff.list');
    // PG11 スタッフ別勤怠一覧画面（管理者）
    Route::get('/admin/attendance/staff/{id}', [AdminController::class, 'staffAttendance'])->name('admin.staff.attendance');
    // 申請詳細画面（管理者）
    Route::get('/admin/stamp_correction_request/{id}', [AdminController::class, 'modificationRequestDetail'])->name('admin.modification-requests.detail');
    // PG13 修正申請承認画面（管理者）
    Route::post('/stamp_correction_request/approve/{attendanceCorrectRequestId}', [AdminController::class, 'approveModificationRequest'])->name('admin.modification-requests.approve');
    // 勤怠直接編集機能（管理者）
    Route::put('/admin/attendance/{id}', [AdminController::class, 'updateAttendance'])->name('admin.attendance.update');
    // CSV出力
    Route::get('/admin/staff/{userId}/attendance/csv', [AdminController::class, 'exportCsv'])->name('admin.staff.attendance.csv');
});
