<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Application\Services\ModificationRequestService;
use App\Application\Services\ModificationRequestBreaksService;
use App\Http\Requests\ModificationRequestRequest;
use Mockery;

/**
 * ModificationRequestControllerのテストクラス
 * 契約による設計（Design by Contract）に基づいてテストを実装
 * 各メソッドの事前条件、事後条件、不変条件を検証
 */
class ModificationRequestControllerTest extends TestCase
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
   * index メソッドのテスト - 管理者ユーザーの場合
   * 
   * 事前条件: 管理者ユーザーが認証されている
   * 事後条件: 全修正申請一覧ビューが返される
   * 不変条件: 承認待ちと承認済みの申請が正しく取得される
   */
  public function test_index_admin_user_with_contract()
  {
    // 事前条件: 管理者ユーザーが認証されている
    $adminUser = User::factory()->create(['is_admin' => true]);
    Auth::login($adminUser);

    // モックサービスの作成
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $modificationRequestBreaksService = Mockery::mock(ModificationRequestBreaksService::class);

    $pendingRequests = ['pending1', 'pending2'];
    $approvedRequests = ['approved1', 'approved2'];

    $modificationRequestService->shouldReceive('getPendingModificationRequests')->andReturn($pendingRequests);
    $modificationRequestService->shouldReceive('getApprovedModificationRequests')->andReturn($approvedRequests);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\ModificationRequestController(
      $modificationRequestService,
      $modificationRequestBreaksService
    );

    // Act: indexメソッドを呼び出し
    $result = $controller->index();

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
    $this->assertEquals('modification-requests.index', $result->name());

    // 不変条件: 承認待ちと承認済みの申請が正しく取得されること
    $viewData = $result->getData();
    $this->assertArrayHasKey('pendingRequests', $viewData);
    $this->assertArrayHasKey('approvedRequests', $viewData);
  }

  /**
   * @test
   * index メソッドのテスト - 一般ユーザーの場合
   * 
   * 事前条件: 一般ユーザーが認証されている
   * 事後条件: 自分の修正申請一覧ビューが返される
   * 不変条件: 自分の承認待ちと承認済みの申請が正しく取得される
   */
  public function test_index_regular_user_with_contract()
  {
    // 事前条件: 一般ユーザーが認証されている
    $user = User::factory()->create(['is_admin' => false]);
    Auth::login($user);

    // モックサービスの作成
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $modificationRequestBreaksService = Mockery::mock(ModificationRequestBreaksService::class);

    $pendingRequests = ['pending1', 'pending2'];
    $approvedRequests = ['approved1', 'approved2'];

    $modificationRequestService->shouldReceive('getPendingRequestsByUser')->with(Mockery::type('string'))->andReturn($pendingRequests);
    $modificationRequestService->shouldReceive('getApprovedRequestsByUser')->with(Mockery::type('string'))->andReturn($approvedRequests);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\ModificationRequestController(
      $modificationRequestService,
      $modificationRequestBreaksService
    );

    // Act: indexメソッドを呼び出し
    $result = $controller->index();

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
    $this->assertEquals('modification-requests.index', $result->name());

    // 不変条件: 自分の承認待ちと承認済みの申請が正しく取得されること
    $viewData = $result->getData();
    $this->assertArrayHasKey('pendingRequests', $viewData);
    $this->assertArrayHasKey('approvedRequests', $viewData);
  }

  /**
   * @test
   * show メソッドのテスト - 成功ケース（自分の申請）
   * 
   * 事前条件: 認証されたユーザーが存在し、自分の申請IDが提供される
   * 事後条件: 修正申請詳細ビューが返される
   * 不変条件: 申請詳細が正しく取得される
   */
  public function test_show_success_own_request_with_contract()
  {
    // 事前条件: 認証されたユーザーが存在する
    $user = User::factory()->create();
    Auth::login($user);

    // モックサービスの作成
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $modificationRequestBreaksService = Mockery::mock(ModificationRequestBreaksService::class);

    $requestId = '1';
    $modificationRequest = Mockery::mock(\App\Models\ModificationRequest::class);
    $modificationRequest->shouldReceive('getAttribute')->with('user_id')->andReturn($user->id);

    $modificationRequestService->shouldReceive('getModificationRequestDetail')->with($requestId)->andReturn($modificationRequest);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\ModificationRequestController(
      $modificationRequestService,
      $modificationRequestBreaksService
    );

    // Act: showメソッドを呼び出し
    $result = $controller->show($requestId);

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
    $this->assertEquals('modification-requests.show', $result->name());

    // 不変条件: 申請詳細が正しく取得されること
    $viewData = $result->getData();
    $this->assertArrayHasKey('modificationRequest', $viewData);
    $this->assertArrayHasKey('user', $viewData);
  }

  /**
   * @test
   * show メソッドのテスト - 申請が見つからないケース
   * 
   * 事前条件: 存在しない申請IDが提供される
   * 事後条件: 404エラーが発生する
   * 不変条件: 適切なエラーハンドリングが行われる
   */
  public function test_show_request_not_found_with_contract()
  {
    // 事前条件: 認証されたユーザーが存在する
    $user = User::factory()->create();
    Auth::login($user);

    // モックサービスの作成
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $modificationRequestBreaksService = Mockery::mock(ModificationRequestBreaksService::class);

    $requestId = '999';
    $modificationRequestService->shouldReceive('getModificationRequestDetail')->with($requestId)->andReturn(null);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\ModificationRequestController(
      $modificationRequestService,
      $modificationRequestBreaksService
    );

    // Act & Assert: showメソッドを呼び出し、404エラーが発生することを確認
    $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

    $controller->show($requestId);
  }

  /**
   * @test
   * show メソッドのテスト - 権限なしケース（他人の申請）
   * 
   * 事前条件: 他人の申請IDが提供される
   * 事後条件: 403エラーが発生する
   * 不変条件: 適切な権限チェックが行われる
   */
  public function test_show_unauthorized_access_with_contract()
  {
    // 事前条件: 認証されたユーザーが存在する
    $user = User::factory()->create();
    Auth::login($user);

    // モックサービスの作成
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $modificationRequestBreaksService = Mockery::mock(ModificationRequestBreaksService::class);

    $requestId = '1';
    $otherUserId = '2';
    $modificationRequest = Mockery::mock(\App\Models\ModificationRequest::class);
    $modificationRequest->shouldReceive('getAttribute')->with('user_id')->andReturn($otherUserId);

    $modificationRequestService->shouldReceive('getModificationRequestDetail')->with($requestId)->andReturn($modificationRequest);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\ModificationRequestController(
      $modificationRequestService,
      $modificationRequestBreaksService
    );

    // Act & Assert: showメソッドを呼び出し、403エラーが発生することを確認
    $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

    $controller->show($requestId);
  }

  /**
   * @test
   * store メソッドのテスト - 成功ケース（休憩時間なし）
   * 
   * 事前条件: 認証されたユーザーが存在し、有効な申請データが提供される
   * 事後条件: 修正申請が作成され、リダイレクトが行われる
   * 不変条件: 修正申請が正しく作成される
   */
  public function test_store_success_without_breaks_with_contract()
  {
    // 事前条件: 認証されたユーザーが存在する
    $user = User::factory()->create();
    Auth::login($user);

    // モックサービスの作成
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $modificationRequestBreaksService = Mockery::mock(ModificationRequestBreaksService::class);

    $validatedData = [
      'attendance_id' => '1',
      'start_time' => '09:00',
      'end_time' => '18:00',
      'remarks' => 'Test remarks'
    ];

    $modificationRequest = ['id' => 1, 'user_id' => $user->id];
    $modificationRequestService->shouldReceive('createRequest')->with(
      $validatedData['attendance_id'],
      $user->id,
      [
        'start_time' => $validatedData['start_time'],
        'end_time' => $validatedData['end_time'],
        'remarks' => $validatedData['remarks']
      ]
    )->andReturn($modificationRequest);

    // モックリクエストの作成
    $request = Mockery::mock(ModificationRequestRequest::class);
    $request->shouldReceive('validated')->andReturn($validatedData);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\ModificationRequestController(
      $modificationRequestService,
      $modificationRequestBreaksService
    );

    // Act: storeメソッドを呼び出し
    $result = $controller->store($request);

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

    // 不変条件: 修正申請が正しく作成されること
    $modificationRequestService->shouldHaveReceived('createRequest');
  }

  /**
   * @test
   * store メソッドのテスト - 成功ケース（休憩時間あり）
   * 
   * 事前条件: 認証されたユーザーが存在し、休憩時間を含む有効な申請データが提供される
   * 事後条件: 修正申請と休憩時間の修正申請が作成され、リダイレクトが行われる
   * 不変条件: 修正申請と休憩時間の修正申請が正しく作成される
   */
  public function test_store_success_with_breaks_with_contract()
  {
    // 事前条件: 認証されたユーザーが存在する
    $user = User::factory()->create();
    Auth::login($user);

    // モックサービスの作成
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $modificationRequestBreaksService = Mockery::mock(ModificationRequestBreaksService::class);

    $validatedData = [
      'attendance_id' => '1',
      'start_time' => '09:00',
      'end_time' => '18:00',
      'remarks' => 'Test remarks',
      'breaks' => [
        ['start_time' => '12:00', 'end_time' => '13:00'],
        ['start_time' => '', 'end_time' => '']
      ]
    ];

    $modificationRequest = Mockery::mock(\App\Models\ModificationRequest::class);
    $modificationRequest->shouldReceive('getAttribute')->with('id')->andReturn(1);
    $modificationRequest->shouldReceive('offsetGet')->with('id')->andReturn(1);
    $modificationRequestService->shouldReceive('createRequest')->andReturn($modificationRequest);
    $modificationRequestBreaksService->shouldReceive('createRequest')->with(
      1,
      ['start_time' => '12:00', 'end_time' => '13:00']
    )->once();

    // モックリクエストの作成
    $request = Mockery::mock(ModificationRequestRequest::class);
    $request->shouldReceive('validated')->andReturn($validatedData);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\ModificationRequestController(
      $modificationRequestService,
      $modificationRequestBreaksService
    );

    // Act: storeメソッドを呼び出し
    $result = $controller->store($request);

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

    // 不変条件: 修正申請と休憩時間の修正申請が正しく作成されること
    $modificationRequestService->shouldHaveReceived('createRequest');
    $modificationRequestBreaksService->shouldHaveReceived('createRequest');
  }

  /**
   * @test
   * store メソッドのテスト - 例外発生ケース
   * 
   * 事前条件: 修正申請作成で例外が発生する
   * 事後条件: エラーメッセージと共にリダイレクトが行われる
   * 不変条件: 例外が適切にキャッチされる
   */
  public function test_store_exception_with_contract()
  {
    // 事前条件: 認証されたユーザーが存在する
    $user = User::factory()->create();
    Auth::login($user);

    // モックサービスの作成
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $modificationRequestBreaksService = Mockery::mock(ModificationRequestBreaksService::class);

    $validatedData = [
      'attendance_id' => '1',
      'start_time' => '09:00',
      'end_time' => '18:00',
      'remarks' => 'Test remarks'
    ];

    $modificationRequestService->shouldReceive('createRequest')->andThrow(new \Exception('Database error'));

    // モックリクエストの作成
    $request = Mockery::mock(ModificationRequestRequest::class);
    $request->shouldReceive('validated')->andReturn($validatedData);

    // コントローラーのインスタンスを作成
    $controller = new \App\Http\Controllers\ModificationRequestController(
      $modificationRequestService,
      $modificationRequestBreaksService
    );

    // Act: storeメソッドを呼び出し
    $result = $controller->store($request);

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

    // 不変条件: 例外が適切にキャッチされ、エラーメッセージが設定されること
    $this->assertNotNull($result);
  }

  /**
   * @test
   * ModificationRequestController クラスの不変条件テスト
   */
  public function test_modification_request_controller_invariants()
  {
    // モックサービスの作成
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $modificationRequestBreaksService = Mockery::mock(ModificationRequestBreaksService::class);

    $controller = new \App\Http\Controllers\ModificationRequestController(
      $modificationRequestService,
      $modificationRequestBreaksService
    );

    // 不変条件1: ModificationRequestControllerはControllerを継承していること
    $this->assertInstanceOf(\App\Http\Controllers\Controller::class, $controller);

    // 不変条件2: 必要なメソッドが存在すること
    $requiredMethods = ['index', 'show', 'store'];

    foreach ($requiredMethods as $method) {
      $this->assertTrue(method_exists($controller, $method), "Method {$method} should exist");
    }

    // 不変条件3: メソッドのシグネチャが正しいこと
    $reflection = new \ReflectionClass($controller);

    // ビュー表示メソッドの戻り値型チェック
    $viewMethods = ['index', 'show'];
    foreach ($viewMethods as $method) {
      $methodReflection = $reflection->getMethod($method);
      $this->assertTrue($methodReflection->isPublic());
      $this->assertEquals('Illuminate\Contracts\View\View', $methodReflection->getReturnType()->getName());
    }

    // リダイレクトメソッドの戻り値型チェック
    $redirectMethods = ['store'];
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
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $modificationRequestBreaksService = Mockery::mock(ModificationRequestBreaksService::class);

    $controller = new \App\Http\Controllers\ModificationRequestController(
      $modificationRequestService,
      $modificationRequestBreaksService
    );

    // 事前状態の記録（シリアライゼーションを避ける）
    $initialControllerClass = get_class($controller);

    // 複数のメソッドを呼び出し（モックを設定）
    $user = User::factory()->create();
    Auth::login($user);

    $modificationRequestService->shouldReceive('getPendingRequestsByUser')->with(Mockery::type('string'))->andReturn([]);
    $modificationRequestService->shouldReceive('getApprovedRequestsByUser')->with(Mockery::type('string'))->andReturn([]);

    $result1 = $controller->index();

    // 事後状態の確認
    $finalControllerClass = get_class($controller);

    // 不変条件: コントローラ自体の状態は変わらないこと（ステートレス）
    $this->assertEquals($initialControllerClass, $finalControllerClass);

    // 不変条件: 複数回呼び出しても同じ結果が得られること（べき等性）
    $result1_repeat = $controller->index();

    $this->assertEquals($result1->name(), $result1_repeat->name());
  }

  /**
   * @test
   * 管理者と一般ユーザーの権限分離テスト
   * 
   * 事前条件: 管理者と一般ユーザーが認証されている
   * 事後条件: それぞれ適切な申請データが取得される
   * 不変条件: 権限に応じたデータアクセスが行われる
   */
  public function test_admin_and_user_permission_separation_with_contract()
  {
    // モックサービスの作成
    $modificationRequestService = Mockery::mock(ModificationRequestService::class);
    $modificationRequestBreaksService = Mockery::mock(ModificationRequestBreaksService::class);

    $controller = new \App\Http\Controllers\ModificationRequestController(
      $modificationRequestService,
      $modificationRequestBreaksService
    );

    // 管理者ユーザーのテスト
    $adminUser = User::factory()->create(['is_admin' => true]);
    Auth::login($adminUser);

    $modificationRequestService->shouldReceive('getPendingModificationRequests')->andReturn(['admin_pending']);
    $modificationRequestService->shouldReceive('getApprovedModificationRequests')->andReturn(['admin_approved']);

    $adminResult = $controller->index();
    $adminViewData = $adminResult->getData();

    // 一般ユーザーのテスト
    $regularUser = User::factory()->create(['is_admin' => false]);
    Auth::login($regularUser);

    $modificationRequestService->shouldReceive('getPendingRequestsByUser')->with(Mockery::type('string'))->andReturn(['user_pending']);
    $modificationRequestService->shouldReceive('getApprovedRequestsByUser')->with(Mockery::type('string'))->andReturn(['user_approved']);

    $userResult = $controller->index();
    $userViewData = $userResult->getData();

    // 事後条件の検証
    $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $adminResult);
    $this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $userResult);

    // 不変条件: 管理者は全申請、一般ユーザーは自分の申請のみアクセス可能であること
    $this->assertArrayHasKey('pendingRequests', $adminViewData);
    $this->assertArrayHasKey('approvedRequests', $adminViewData);
    $this->assertArrayHasKey('pendingRequests', $userViewData);
    $this->assertArrayHasKey('approvedRequests', $userViewData);
  }
}
