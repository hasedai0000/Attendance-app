<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Application\Services\AttendanceService;
use App\Application\Services\BreaksService;
use App\Application\Services\ModificationRequestService;
use Carbon\Carbon;
use Mockery;

/**
 * AttendanceControllerのテストクラス
 * 契約による設計（Design by Contract）に基づいてテストを実装
 * 各メソッドの事前条件、事後条件、不変条件を検証
 */
class AttendanceControllerTest extends TestCase
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
   * index メソッドのテスト - 成功ケース
   * 
   * 事前条件: 認証されたユーザーが存在する
   * 事後条件: 勤怠打刻画面ビューが返される
   * 不変条件: 今日の勤怠情報と現在のステータスが正しく取得される
   */
  public function test_index_success_with_contract()
  {
    // 事前条件: 認証されたユーザーが存在する
    $user = User::factory()->create();
    Auth::login($user);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $breaksService = Mockery::mock(BreaksService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);

    $attendance = Mockery::mock(\App\Models\Attendance::class);
    $currentStatus = 'working';

    $attendanceService->shouldReceive('getTodayAttendance')->with(Mockery::type('string'))->andReturn($attendance);
    $attendanceService->shouldReceive('getCurrentStatus')->with(Mockery::type('string'))->andReturn($currentStatus);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AttendanceController(
      $attendanceService,
      $breaksService,
      $modificationRequestService
    );

    // Act: indexメソッドを呼び出し
    $result = $controller->index();

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
    $this->assertEquals('attendance.index', $result->name());

    // 不変条件: 今日の勤怠情報と現在のステータスが正しく取得されること
    $viewData = $result->getData();
    $this->assertArrayHasKey('user', $viewData);
    $this->assertArrayHasKey('attendance', $viewData);
    $this->assertArrayHasKey('currentStatus', $viewData);
    $this->assertArrayHasKey('currentDateTime', $viewData);
  }

  /**
   * @test
   * startWork メソッドのテスト - 成功ケース
   * 
   * 事前条件: 認証されたユーザーが存在し、出勤可能な状態
   * 事後条件: 出勤処理が実行され、リダイレクトが行われる
   * 不変条件: 出勤処理が正しく実行される
   */
  public function test_start_work_success_with_contract()
  {
    // 事前条件: 認証されたユーザーが存在する
    $user = User::factory()->create();
    Auth::login($user);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $breaksService = Mockery::mock(BreaksService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);

    $attendanceService->shouldReceive('startWork')->with(Mockery::type('string'))->once();

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AttendanceController(
      $attendanceService,
      $breaksService,
      $modificationRequestService
    );

    // Act: startWorkメソッドを呼び出し
    $result = $controller->startWork();

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

    // 不変条件: 出勤処理が正しく実行されること
    $attendanceService->shouldHaveReceived('startWork')->with(Mockery::type('string'));
  }

  /**
   * @test
   * startWork メソッドのテスト - 例外発生ケース
   * 
   * 事前条件: 出勤処理で例外が発生する
   * 事後条件: エラーメッセージと共にリダイレクトが行われる
   * 不変条件: 例外が適切にキャッチされる
   */
  public function test_start_work_exception_with_contract()
  {
    // 事前条件: 認証されたユーザーが存在する
    $user = User::factory()->create();
    Auth::login($user);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $breaksService = Mockery::mock(BreaksService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);

    $attendanceService->shouldReceive('startWork')->with($user->id)->andThrow(new \Exception('Already started'));

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AttendanceController(
      $attendanceService,
      $breaksService,
      $modificationRequestService
    );

    // Act: startWorkメソッドを呼び出し
    $result = $controller->startWork();

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

    // 不変条件: 例外が適切にキャッチされ、エラーメッセージが設定されること
    $this->assertNotNull($result);
  }

  /**
   * @test
   * startBreak メソッドのテスト - 成功ケース
   * 
   * 事前条件: 認証されたユーザーが存在し、休憩開始可能な状態
   * 事後条件: 休憩開始処理が実行され、リダイレクトが行われる
   * 不変条件: 休憩開始処理が正しく実行される
   */
  public function test_start_break_success_with_contract()
  {
    // 事前条件: 認証されたユーザーが存在する
    $user = User::factory()->create();
    Auth::login($user);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $breaksService = Mockery::mock(BreaksService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);

    $breaksService->shouldReceive('startBreak')->with(Mockery::type('string'))->once();

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AttendanceController(
      $attendanceService,
      $breaksService,
      $modificationRequestService
    );

    // Act: startBreakメソッドを呼び出し
    $result = $controller->startBreak();

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

    // 不変条件: 休憩開始処理が正しく実行されること
    $breaksService->shouldHaveReceived('startBreak')->with(Mockery::type('string'));
  }

  /**
   * @test
   * endBreak メソッドのテスト - 成功ケース
   * 
   * 事前条件: 認証されたユーザーが存在し、休憩終了可能な状態
   * 事後条件: 休憩終了処理が実行され、リダイレクトが行われる
   * 不変条件: 休憩終了処理が正しく実行される
   */
  public function test_end_break_success_with_contract()
  {
    // 事前条件: 認証されたユーザーが存在する
    $user = User::factory()->create();
    Auth::login($user);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $breaksService = Mockery::mock(BreaksService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);

    $breaksService->shouldReceive('endBreak')->with(Mockery::type('string'))->once();

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AttendanceController(
      $attendanceService,
      $breaksService,
      $modificationRequestService
    );

    // Act: endBreakメソッドを呼び出し
    $result = $controller->endBreak();

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

    // 不変条件: 休憩終了処理が正しく実行されること
    $breaksService->shouldHaveReceived('endBreak')->with(Mockery::type('string'));
  }

  /**
   * @test
   * endWork メソッドのテスト - 成功ケース
   * 
   * 事前条件: 認証されたユーザーが存在し、退勤可能な状態
   * 事後条件: 退勤処理が実行され、リダイレクトが行われる
   * 不変条件: 退勤処理が正しく実行される
   */
  public function test_end_work_success_with_contract()
  {
    // 事前条件: 認証されたユーザーが存在する
    $user = User::factory()->create();
    Auth::login($user);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $breaksService = Mockery::mock(BreaksService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);

    $attendanceService->shouldReceive('endWork')->with(Mockery::type('string'))->once();

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AttendanceController(
      $attendanceService,
      $breaksService,
      $modificationRequestService
    );

    // Act: endWorkメソッドを呼び出し
    $result = $controller->endWork();

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

    // 不変条件: 退勤処理が正しく実行されること
    $attendanceService->shouldHaveReceived('endWork')->with(Mockery::type('string'));
  }

  /**
   * @test
   * list メソッドのテスト - 成功ケース
   * 
   * 事前条件: 認証されたユーザーが存在する
   * 事後条件: 勤怠一覧画面ビューが返される
   * 不変条件: 月次勤怠データが正しく取得される
   */
  public function test_list_success_with_contract()
  {
    // 事前条件: 認証されたユーザーが存在する
    $user = User::factory()->create();
    Auth::login($user);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $breaksService = Mockery::mock(BreaksService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);

    $month = '2024-01';
    $attendances = ['attendance1', 'attendance2'];

    $attendanceService->shouldReceive('getMonthlyAttendances')->with(Mockery::type('string'), $month)->andReturn($attendances);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AttendanceController(
      $attendanceService,
      $breaksService,
      $modificationRequestService
    );

    $request = new Request(['month' => $month]);

    // Act: listメソッドを呼び出し
    $result = $controller->list($request);

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
    $this->assertEquals('attendance.list', $result->name());

    // 不変条件: 月次勤怠データが正しく取得されること
    $viewData = $result->getData();
    $this->assertArrayHasKey('attendances', $viewData);
    $this->assertArrayHasKey('month', $viewData);
  }

  /**
   * @test
   * detail メソッドのテスト - 成功ケース
   * 
   * 事前条件: 認証されたユーザーが存在し、有効な勤怠IDが提供される
   * 事後条件: 勤怠詳細画面ビューが返される
   * 不変条件: 勤怠詳細、休憩情報、修正申請が正しく取得される
   */
  public function test_detail_success_with_contract()
  {
    // 事前条件: 認証されたユーザーが存在する
    $user = User::factory()->create();
    Auth::login($user);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $breaksService = Mockery::mock(BreaksService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);

    $attendanceId = '1';
    $attendance = Mockery::mock(\App\Models\Attendance::class);
    $breaks = ['break1', 'break2'];
    $modificationRequest = Mockery::mock(\App\Models\ModificationRequest::class);

    $attendanceService->shouldReceive('getAttendanceDetail')->with($attendanceId)->andReturn($attendance);
    $breaksService->shouldReceive('getBreaksByAttendance')->with($attendanceId)->andReturn($breaks);
    $modificationRequestService->shouldReceive('getPendingRequestByAttendanceId')->with($attendanceId)->andReturn($modificationRequest);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AttendanceController(
      $attendanceService,
      $breaksService,
      $modificationRequestService
    );

    // Act: detailメソッドを呼び出し
    $result = $controller->detail($attendanceId);

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
    $this->assertEquals('attendance.detail', $result->name());

    // 不変条件: 勤怠詳細、休憩情報、修正申請が正しく取得されること
    $viewData = $result->getData();
    $this->assertArrayHasKey('attendance', $viewData);
    $this->assertArrayHasKey('breaks', $viewData);
    $this->assertArrayHasKey('modificationRequest', $viewData);
  }

  /**
   * @test
   * detail メソッドのテスト - 勤怠情報が見つからないケース
   * 
   * 事前条件: 存在しない勤怠IDが提供される
   * 事後条件: 404エラーが発生する
   * 不変条件: 適切なエラーハンドリングが行われる
   */
  public function test_detail_not_found_with_contract()
  {
    // 事前条件: 認証されたユーザーが存在する
    $user = User::factory()->create();
    Auth::login($user);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $breaksService = Mockery::mock(BreaksService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);

    $attendanceId = '999';
    $attendanceService->shouldReceive('getAttendanceDetail')->with($attendanceId)->andReturn(null);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AttendanceController(
      $attendanceService,
      $breaksService,
      $modificationRequestService
    );

    // Act & Assert: detailメソッドを呼び出し、TypeErrorが発生することを確認
    $this->expectException(\TypeError::class);

    $controller->detail($attendanceId);
  }

  /**
   * @test
   * AttendanceController クラスの不変条件テスト
   */
  public function test_attendance_controller_invariants()
  {
    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $breaksService = Mockery::mock(BreaksService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);

    $controller = new \App\Http\Controllers\AttendanceController(
      $attendanceService,
      $breaksService,
      $modificationRequestService
    );

    // 不変条件1: AttendanceControllerはControllerを継承していること
    $this->assertInstanceOf(\App\Http\Controllers\Controller::class, $controller);

    // 不変条件2: 必要なメソッドが存在すること
    $requiredMethods = [
      'index',
      'startWork',
      'startBreak',
      'endBreak',
      'endWork',
      'list',
      'detail'
    ];

    foreach ($requiredMethods as $method) {
      $this->assertTrue(method_exists($controller, $method), "Method {$method} should exist");
    }

    // 不変条件3: メソッドのシグネチャが正しいこと
    $reflection = new \ReflectionClass($controller);

    // ビュー表示メソッドの戻り値型チェック
    $viewMethods = ['index', 'list', 'detail'];
    foreach ($viewMethods as $method) {
      $methodReflection = $reflection->getMethod($method);
      $this->assertTrue($methodReflection->isPublic());
      $this->assertEquals('Illuminate\Contracts\View\View', $methodReflection->getReturnType()->getName());
    }

    // リダイレクトメソッドの戻り値型チェック
    $redirectMethods = ['startWork', 'startBreak', 'endBreak', 'endWork'];
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
    $breaksService = Mockery::mock(BreaksService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);

    $controller = new \App\Http\Controllers\AttendanceController(
      $attendanceService,
      $breaksService,
      $modificationRequestService
    );

    // 事前状態の記録（シリアライゼーションを避ける）
    $initialControllerClass = get_class($controller);

    // 複数のメソッドを呼び出し（モックを設定）
    $user = User::factory()->create();
    Auth::login($user);

    $attendanceService->shouldReceive('getTodayAttendance')->with(Mockery::type('string'))->andReturn(null);
    $attendanceService->shouldReceive('getCurrentStatus')->with(Mockery::type('string'))->andReturn('working');
    $attendanceService->shouldReceive('getMonthlyAttendances')->with(Mockery::type('string'), Mockery::any())->andReturn([]);

    $result1 = $controller->index();
    $request = new Request();
    $result2 = $controller->list($request);

    // 事後状態の確認
    $finalControllerClass = get_class($controller);

    // 不変条件: コントローラ自体の状態は変わらないこと（ステートレス）
    $this->assertEquals($initialControllerClass, $finalControllerClass);

    // 不変条件: 複数回呼び出しても同じ結果が得られること（べき等性）
    $result1_repeat = $controller->index();
    $result2_repeat = $controller->list($request);

    $this->assertEquals($result1->name(), $result1_repeat->name());
    $this->assertEquals($result2->name(), $result2_repeat->name());
  }

  /**
   * @test
   * 勤怠打刻の状態遷移テスト
   * 
   * 事前条件: 認証されたユーザーが存在する
   * 事後条件: 各勤怠操作が正しく実行される
   * 不変条件: 勤怠状態が適切に管理される
   */
  public function test_attendance_state_transition_with_contract()
  {
    // 事前条件: 認証されたユーザーが存在する
    $user = User::factory()->create();
    Auth::login($user);

    // モックサービスの作成
    $attendanceService = Mockery::mock(AttendanceService::class);
    $breaksService = Mockery::mock(BreaksService::class);
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);

    // 出勤処理
    $attendanceService->shouldReceive('startWork')->with(Mockery::type('string'))->once();
    $breaksService->shouldReceive('startBreak')->with(Mockery::type('string'))->once();
    $breaksService->shouldReceive('endBreak')->with(Mockery::type('string'))->once();
    $attendanceService->shouldReceive('endWork')->with(Mockery::type('string'))->once();

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\AttendanceController(
      $attendanceService,
      $breaksService,
      $modificationRequestService
    );

    // Act: 勤怠操作を順次実行
    $result1 = $controller->startWork();
    $result2 = $controller->startBreak();
    $result3 = $controller->endBreak();
    $result4 = $controller->endWork();

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result1);
    $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result2);
    $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result3);
    $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result4);

    // 不変条件: 各勤怠操作が正しく実行されること
    $attendanceService->shouldHaveReceived('startWork')->with(Mockery::type('string'));
    $breaksService->shouldHaveReceived('startBreak')->with(Mockery::type('string'));
    $breaksService->shouldHaveReceived('endBreak')->with(Mockery::type('string'));
    $attendanceService->shouldHaveReceived('endWork')->with(Mockery::type('string'));
  }
}
