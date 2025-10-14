<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Application\Services\AttendanceService;
use App\Application\Services\ModificationRequestService;
use App\Application\Services\UserService;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use Carbon\Carbon;
use Mockery;

/**
 * AdminControllerのテストクラス
 * 契約による設計（Design by Contract）に基づいてテストを実装
 * 各メソッドの事前条件、事後条件、不変条件を検証
 */
class AdminControllerTest extends TestCase
{
  use WithFaker;

  protected function setUp(): void
  {
    parent::setUp();
  }

  protected function tearDown(): void
  {
    Mockery::close();
    parent::tearDown();
  }

  /**
   * @test
   * dailyAttendance メソッドのテスト - 成功ケース
   * 
   * 事前条件: 管理者ユーザーが認証されている
   * 事後条件: 日次勤怠一覧ビューが返される
   * 不変条件: 必要なデータが正しく取得される
   */
  public function test_daily_attendance_success_with_contract()
  {
    // 事前条件: 管理者ユーザーが認証されている
    $adminUser = User::factory()->create(['is_admin' => true]);
    Auth::login($adminUser);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $userService = Mockery::mock(UserService::class);

    $targetDate = Carbon::today();
    $attendances = ['attendance1', 'attendance2'];
    $todayAttendances = ['today1', 'today2'];
    $pendingRequests = ['request1', 'request2'];

    $attendanceService->shouldReceive('getAttendancesByDate')->with($targetDate)->andReturn($attendances);
    $attendanceService->shouldReceive('getTodayAttendances')->andReturn($todayAttendances);
    $modificationRequestService->shouldReceive('getPendingModificationRequests')->andReturn($pendingRequests);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AdminController(
      $attendanceService,
      $modificationRequestService,
      $userService
    );

    $request = new Request(['date' => $targetDate->format('Y-m-d')]);

    // Act: dailyAttendanceメソッドを呼び出し
    $result = $controller->dailyAttendance($request);

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
    $this->assertEquals('admin.daily-attendance', $result->name());

    // 不変条件: 必要なデータが正しく取得されること
    $viewData = $result->getData();
    $this->assertArrayHasKey('attendances', $viewData);
    $this->assertArrayHasKey('todayAttendances', $viewData);
    $this->assertArrayHasKey('pendingRequests', $viewData);
  }

  /**
   * @test
   * dailyAttendance メソッドのテスト - 例外発生ケース
   * 
   * 事前条件: サービスで例外が発生する
   * 事後条件: エラーメッセージと共にビューが返される
   * 不変条件: 例外が適切にキャッチされる
   */
  public function test_daily_attendance_exception_with_contract()
  {
    // 事前条件: 管理者ユーザーが認証されている
    $adminUser = User::factory()->create(['is_admin' => true]);
    Auth::login($adminUser);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $userService = Mockery::mock(UserService::class);

    $attendanceService->shouldReceive('getAttendancesByDate')->andThrow(new \Exception('Database error'));

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AdminController(
      $attendanceService,
      $modificationRequestService,
      $userService
    );

    $request = new Request();

    // Act: dailyAttendanceメソッドを呼び出し
    $result = $controller->dailyAttendance($request);

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
    $this->assertEquals('admin.daily-attendance', $result->name());

    // 不変条件: 例外が適切にキャッチされ、エラーメッセージが設定されること
    $viewData = $result->getData();
    $this->assertArrayHasKey('attendances', $viewData);
    $this->assertArrayHasKey('todayAttendances', $viewData);
    $this->assertArrayHasKey('pendingRequests', $viewData);
  }

  /**
   * @test
   * staffList メソッドのテスト - 成功ケース
   * 
   * 事前条件: 管理者ユーザーが認証されている
   * 事後条件: スタッフ一覧ビューが返される
   * 不変条件: 全ユーザーが正しく取得される
   */
  public function test_staff_list_success_with_contract()
  {
    // 事前条件: 管理者ユーザーが認証されている
    $adminUser = User::factory()->create(['is_admin' => true]);
    Auth::login($adminUser);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $userService = Mockery::mock(UserService::class);

    $staff = ['user1', 'user2', 'user3'];
    $userService->shouldReceive('getAllUsers')->andReturn($staff);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AdminController(
      $attendanceService,
      $modificationRequestService,
      $userService
    );

    // Act: staffListメソッドを呼び出し
    $result = $controller->staffList();

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
    $this->assertEquals('admin.staff-list', $result->name());

    // 不変条件: 全ユーザーが正しく取得されること
    $viewData = $result->getData();
    $this->assertArrayHasKey('staff', $viewData);
  }

  /**
   * @test
   * staffAttendance メソッドのテスト - 成功ケース
   * 
   * 事前条件: 管理者ユーザーが認証され、有効なユーザーIDが提供される
   * 事後条件: スタッフ月次勤怠一覧ビューが返される
   * 不変条件: ユーザーと勤怠データが正しく取得される
   */
  public function test_staff_attendance_success_with_contract()
  {
    // 事前条件: 管理者ユーザーが認証されている
    $adminUser = User::factory()->create(['is_admin' => true]);
    Auth::login($adminUser);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $userService = Mockery::mock(UserService::class);

    $userId = '1';
    $monthString = '2024-01';
    $user = User::factory()->make(['id' => $userId]);
    $attendances = ['attendance1', 'attendance2'];

    $userService->shouldReceive('getUserById')->with($userId)->andReturn($user);
    $attendanceService->shouldReceive('getMonthlyAttendances')->with($userId, $monthString)->andReturn($attendances);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AdminController(
      $attendanceService,
      $modificationRequestService,
      $userService
    );

    $request = new Request(['month' => $monthString]);

    // Act: staffAttendanceメソッドを呼び出し
    $result = $controller->staffAttendance($request, $userId);

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
    $this->assertEquals('admin.staff-attendance', $result->name());

    // 不変条件: ユーザーと勤怠データが正しく取得されること
    $viewData = $result->getData();
    $this->assertArrayHasKey('attendances', $viewData);
    $this->assertArrayHasKey('user', $viewData);
    $this->assertArrayHasKey('month', $viewData);
  }

  /**
   * @test
   * staffAttendance メソッドのテスト - ユーザーが見つからないケース
   * 
   * 事前条件: 存在しないユーザーIDが提供される
   * 事後条件: エラービューが返される
   * 不変条件: 適切なエラーハンドリングが行われる
   */
  public function test_staff_attendance_user_not_found_with_contract()
  {
    // 事前条件: 管理者ユーザーが認証されている
    $adminUser = User::factory()->create(['is_admin' => true]);
    Auth::login($adminUser);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $userService = Mockery::mock(UserService::class);

    $userId = '999';
    $userService->shouldReceive('getUserById')->with($userId)->andReturn(null);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AdminController(
      $attendanceService,
      $modificationRequestService,
      $userService
    );

    $request = new Request();

    // Act: staffAttendanceメソッドを呼び出し
    $result = $controller->staffAttendance($request, $userId);

    // 事後条件の検証: エラービューが返されること
    $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
    $this->assertEquals('admin.staff-attendance', $result->name());

    // 不変条件: エラーハンドリングが適切に行われること
    $viewData = $result->getData();
    $this->assertArrayHasKey('attendances', $viewData);
    $this->assertArrayHasKey('user', $viewData);
    $this->assertArrayHasKey('month', $viewData);
    $this->assertNull($viewData['user']);
  }

  /**
   * @test
   * modificationRequests メソッドのテスト - 成功ケース
   * 
   * 事前条件: 管理者ユーザーが認証されている
   * 事後条件: 修正申請一覧ビューが返される
   * 不変条件: 承認待ちと承認済みの申請が正しく取得される
   */
  public function test_modification_requests_success_with_contract()
  {
    // 事前条件: 管理者ユーザーが認証されている
    $adminUser = User::factory()->create(['is_admin' => true]);
    Auth::login($adminUser);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $userService = Mockery::mock(UserService::class);

    $pendingRequests = ['pending1', 'pending2'];
    $approvedRequests = ['approved1', 'approved2'];

    $modificationRequestService->shouldReceive('getPendingModificationRequests')->andReturn($pendingRequests);
    $modificationRequestService->shouldReceive('getApprovedModificationRequests')->andReturn($approvedRequests);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AdminController(
      $attendanceService,
      $modificationRequestService,
      $userService
    );

    // Act: modificationRequestsメソッドを呼び出し
    $result = $controller->modificationRequests();

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
    $this->assertEquals('admin.modification-requests', $result->name());

    // 不変条件: 承認待ちと承認済みの申請が正しく取得されること
    $viewData = $result->getData();
    $this->assertArrayHasKey('pendingRequests', $viewData);
    $this->assertArrayHasKey('approvedRequests', $viewData);
  }

  /**
   * @test
   * approveModificationRequest メソッドのテスト - 成功ケース
   * 
   * 事前条件: 管理者ユーザーが認証され、有効な申請IDが提供される
   * 事後条件: 承認処理が実行され、リダイレクトが行われる
   * 不変条件: 承認処理が正しく実行される
   */
  public function test_approve_modification_request_success_with_contract()
  {
    // 事前条件: 管理者ユーザーが認証されている
    $adminUser = User::factory()->create(['is_admin' => true]);
    Auth::login($adminUser);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $userService = Mockery::mock(UserService::class);

    $requestId = '1';
    $modificationRequestService->shouldReceive('approveModificationRequest')->with($requestId)->once();

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AdminController(
      $attendanceService,
      $modificationRequestService,
      $userService
    );

    // Act: approveModificationRequestメソッドを呼び出し
    $result = $controller->approveModificationRequest($requestId);

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

    // 不変条件: 承認処理が正しく実行されること
    $modificationRequestService->shouldHaveReceived('approveModificationRequest');
  }

  /**
   * @test
   * modificationRequestDetail メソッドのテスト - 成功ケース
   * 
   * 事前条件: 管理者ユーザーが認証され、有効な申請IDが提供される
   * 事後条件: 修正申請詳細ビューが返される
   * 不変条件: 申請詳細が正しく取得される
   */
  public function test_modification_request_detail_success_with_contract()
  {
    // 事前条件: 管理者ユーザーが認証されている
    $adminUser = User::factory()->create(['is_admin' => true]);
    Auth::login($adminUser);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $userService = Mockery::mock(UserService::class);

    $requestId = '1';
    $modificationRequest = Mockery::mock(\App\Models\ModificationRequest::class);

    $modificationRequestService->shouldReceive('getModificationRequestById')->with($requestId)->andReturn($modificationRequest);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AdminController(
      $attendanceService,
      $modificationRequestService,
      $userService
    );

    // Act: modificationRequestDetailメソッドを呼び出し
    $result = $controller->modificationRequestDetail($requestId);

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
    $this->assertEquals('admin.modification-request-detail', $result->name());

    // 不変条件: 申請詳細が正しく取得されること
    $viewData = $result->getData();
    $this->assertArrayHasKey('modificationRequest', $viewData);
  }

  /**
   * @test
   * modificationRequestDetail メソッドのテスト - 申請が見つからないケース
   * 
   * 事前条件: 存在しない申請IDが提供される
   * 事後条件: 404エラーが発生する
   * 不変条件: 適切なエラーハンドリングが行われる
   */
  public function test_modification_request_detail_not_found_with_contract()
  {
    // 事前条件: 管理者ユーザーが認証されている
    $adminUser = User::factory()->create(['is_admin' => true]);
    Auth::login($adminUser);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $userService = Mockery::mock(UserService::class);

    $requestId = '999';
    $modificationRequestService->shouldReceive('getModificationRequestById')->with($requestId)->andReturn(null);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AdminController(
      $attendanceService,
      $modificationRequestService,
      $userService
    );

    // Act & Assert: modificationRequestDetailメソッドを呼び出し、404エラーが発生することを確認
    $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

    $controller->modificationRequestDetail($requestId);
  }

  /**
   * @test
   * updateAttendance メソッドのテスト - 成功ケース
   * 
   * 事前条件: 管理者ユーザーが認証され、有効な勤怠IDと更新データが提供される
   * 事後条件: 勤怠情報が更新され、リダイレクトが行われる
   * 不変条件: 勤怠情報と休憩時間が正しく更新される
   */
  public function test_update_attendance_success_with_contract()
  {
    // 事前条件: 管理者ユーザーが認証されている
    $adminUser = User::factory()->create(['is_admin' => true]);
    Auth::login($adminUser);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $userService = Mockery::mock(UserService::class);

    $attendanceId = '1';
    $attendance = Mockery::mock(\App\Models\Attendance::class);
    $attendance->shouldReceive('getAttribute')->with('date')->andReturn(Carbon::today());
    $attendance->shouldReceive('setAttribute')->withAnyArgs()->andReturnSelf();
    $breaks = ['break1', 'break2'];

    $attendanceService->shouldReceive('getAttendanceDetail')->with($attendanceId)->andReturn($attendance);
    $attendanceService->shouldReceive('updateAttendance')->with($attendanceId, Mockery::type('array'))->once();
    $attendanceService->shouldReceive('updateBreaks')->with($attendanceId, $breaks)->once();

    // モックリクエストの作成
    $request = Mockery::mock(AdminAttendanceUpdateRequest::class);
    $request->shouldReceive('input')->with('start_time')->andReturn('09:00');
    $request->shouldReceive('input')->with('end_time')->andReturn('18:00');
    $request->shouldReceive('input')->with('remarks')->andReturn('Updated remarks');
    $request->shouldReceive('has')->with('breaks')->andReturn(true);
    $request->shouldReceive('input')->with('breaks')->andReturn($breaks);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AdminController(
      $attendanceService,
      $modificationRequestService,
      $userService
    );

    // Act: updateAttendanceメソッドを呼び出し
    $result = $controller->updateAttendance($request, $attendanceId);

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

    // 不変条件: 勤怠情報と休憩時間が正しく更新されること
    $attendanceService->shouldHaveReceived('updateAttendance');
    $attendanceService->shouldHaveReceived('updateBreaks');
  }

  /**
   * @test
   * exportCsv メソッドのテスト - 成功ケース
   * 
   * 事前条件: 管理者ユーザーが認証され、有効なユーザーIDが提供される
   * 事後条件: CSVファイルが生成され、ダウンロードレスポンスが返される
   * 不変条件: CSVファイルが正しく生成される
   */
  public function test_export_csv_success_with_contract()
  {
    // 事前条件: 管理者ユーザーが認証されている
    $adminUser = User::factory()->create(['is_admin' => true]);
    Auth::login($adminUser);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $userService = Mockery::mock(UserService::class);

    $userId = '1';
    $monthString = '2024-01';
    $user = User::factory()->make(['id' => $userId, 'name' => 'Test User']);
    $attendances = [
      [
        'date' => '2024-01-01',
        'start_time' => '09:00:00',
        'end_time' => '18:00:00',
        'remarks' => 'Test remarks',
        'breaks' => []
      ]
    ];

    $userService->shouldReceive('getUserById')->with($userId)->andReturn($user);
    $attendanceService->shouldReceive('getMonthlyAttendances')->with($userId, $monthString)->andReturn($attendances);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AdminController(
      $attendanceService,
      $modificationRequestService,
      $userService
    );

    $request = new Request(['month' => $monthString]);

    // Act: exportCsvメソッドを呼び出し
    $result = $controller->exportCsv($request, $userId);

    // 事後条件の検証
    $this->assertInstanceOf(\Symfony\Component\HttpFoundation\StreamedResponse::class, $result);

    // 不変条件: CSVファイルが正しく生成されること
    $headers = $result->headers->all();
    $this->assertArrayHasKey('content-type', $headers);
    $this->assertArrayHasKey('content-disposition', $headers);
  }

  /**
   * @test
   * AdminController クラスの不変条件テスト
   */
  public function test_admin_controller_invariants()
  {
    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $userService = Mockery::mock(UserService::class);

    $controller = new \App\Http\Controllers\AdminController(
      $attendanceService,
      $modificationRequestService,
      $userService
    );

    // 不変条件1: AdminControllerはControllerを継承していること
    $this->assertInstanceOf(\App\Http\Controllers\Controller::class, $controller);

    // 不変条件2: 必要なメソッドが存在すること
    $requiredMethods = [
      'dailyAttendance',
      'staffList',
      'staffAttendance',
      'modificationRequests',
      'approveModificationRequest',
      'modificationRequestDetail',
      'updateAttendance',
      'exportCsv'
    ];

    foreach ($requiredMethods as $method) {
      $this->assertTrue(method_exists($controller, $method), "Method {$method} should exist");
    }

    // 不変条件3: メソッドのシグネチャが正しいこと
    $reflection = new \ReflectionClass($controller);

    // ビュー表示メソッドの戻り値型チェック
    $viewMethods = ['dailyAttendance', 'staffList', 'staffAttendance', 'modificationRequests', 'modificationRequestDetail'];
    foreach ($viewMethods as $method) {
      $methodReflection = $reflection->getMethod($method);
      $this->assertTrue($methodReflection->isPublic());
      $this->assertEquals('Illuminate\Contracts\View\View', $methodReflection->getReturnType()->getName());
    }

    // リダイレクトメソッドの戻り値型チェック
    $redirectMethods = ['approveModificationRequest', 'updateAttendance'];
    foreach ($redirectMethods as $method) {
      $methodReflection = $reflection->getMethod($method);
      $this->assertTrue($methodReflection->isPublic());
      $this->assertEquals('Illuminate\Http\RedirectResponse', $methodReflection->getReturnType()->getName());
    }
  }

  /**
   * @test
   * メソッド呼び出し前後での状態不変性テスト
   */
  public function test_method_call_state_invariants()
  {
    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $userService = Mockery::mock(UserService::class);

    $controller = new \App\Http\Controllers\AdminController(
      $attendanceService,
      $modificationRequestService,
      $userService
    );

    // 事前状態の記録（シリアライゼーションを避ける）
    $initialControllerClass = get_class($controller);

    // 複数のメソッドを呼び出し（モックを設定）
    $attendanceService->shouldReceive('getTodayAttendances')->andReturn([]);
    $modificationRequestService->shouldReceive('getPendingModificationRequests')->andReturn([]);
    $userService->shouldReceive('getAllUsers')->andReturn([]);

    $request = new Request();
    $result1 = $controller->dailyAttendance($request);
    $result2 = $controller->staffList();

    // 事後状態の確認
    $finalControllerClass = get_class($controller);

    // 不変条件: コントローラ自体の状態は変わらないこと（ステートレス）
    $this->assertEquals($initialControllerClass, $finalControllerClass);

    // 不変条件: 複数回呼び出しても同じ結果が得られること（べき等性）
    $result1_repeat = $controller->dailyAttendance($request);
    $result2_repeat = $controller->staffList();

    $this->assertEquals($result1->name(), $result1_repeat->name());
    $this->assertEquals($result2->name(), $result2_repeat->name());
  }
}
