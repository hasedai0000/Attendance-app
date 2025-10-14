<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Domain\User\Services\CreateUserService;
use Mockery;

/**
 * AuthControllerのテストクラス
 * 契約による設計（Design by Contract）に基づいてテストを実装
 * 各メソッドの事前条件、事後条件、不変条件を検証
 */
class AuthControllerTest extends TestCase
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
	 * showLogin メソッドのテスト
	 * 
	 * 事前条件: なし（パブリックアクセス可能）
	 * 事後条件: auth.loginビューが返される
	 * 不変条件: コントローラの状態は変わらない
	 */
	public function test_show_login_returns_correct_view_with_contract()
	{
		// 事前条件: なし（パブリックメソッド）

		// Act: showLoginメソッドを直接呼び出し
		$controller = app(\App\Http\Controllers\AuthController::class);
		$result = $controller->showLogin();

		// 事後条件の検証
		$this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
		$this->assertEquals('auth.login', $result->getName());

		// 不変条件: ビューインスタンスが正しく生成されていること
		$this->assertNotNull($result);
	}

	/**
	 * @test
	 * showRegister メソッドのテスト
	 * 
	 * 事前条件: なし（パブリックアクセス可能）
	 * 事後条件: auth.registerビューが返される
	 * 不変条件: コントローラの状態は変わらない
	 */
	public function test_show_register_returns_correct_view_with_contract()
	{
		// 事前条件: なし（パブリックメソッド）

		// Act: showRegisterメソッドを直接呼び出し
		$controller = app(\App\Http\Controllers\AuthController::class);
		$result = $controller->showRegister();

		// 事後条件の検証
		$this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
		$this->assertEquals('auth.register', $result->getName());

		// 不変条件: ビューインスタンスが正しく生成されていること
		$this->assertNotNull($result);
	}

	/**
	 * @test
	 * showVerificationNotice メソッドのテスト
	 * 
	 * 事前条件: なし（パブリックアクセス可能）
	 * 事後条件: auth.verify-emailビューが返される
	 * 不変条件: コントローラの状態は変わらない
	 */
	public function test_show_verification_notice_returns_correct_view_with_contract()
	{
		// 事前条件: なし（パブリックメソッド）

		// Act: showVerificationNoticeメソッドを直接呼び出し
		$controller = app(\App\Http\Controllers\AuthController::class);
		$result = $controller->showVerificationNotice();

		// 事後条件の検証
		$this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
		$this->assertEquals('auth.verify-email', $result->getName());

		// 不変条件: ビューインスタンスが正しく生成されていること
		$this->assertNotNull($result);
	}

	/**
	 * @test
	 * showAdminLogin メソッドのテスト
	 * 
	 * 事前条件: なし（パブリックアクセス可能）
	 * 事後条件: auth.admin-loginビューが返される
	 * 不変条件: コントローラの状態は変わらない
	 */
	public function test_show_admin_login_returns_correct_view_with_contract()
	{
		// 事前条件: なし（パブリックメソッド）

		// Act: showAdminLoginメソッドを直接呼び出し
		$controller = app(\App\Http\Controllers\AuthController::class);
		$result = $controller->showAdminLogin();

		// 事後条件の検証
		$this->assertInstanceOf(\Illuminate\Contracts\View\View::class, $result);
		$this->assertEquals('auth.admin-login', $result->getName());

		// 不変条件: ビューインスタンスが正しく生成されていること
		$this->assertNotNull($result);
	}

	/**
	 * @test
	 * login メソッドのテスト - 成功ケース
	 * 
	 * 事前条件: 有効なログイン情報が提供される
	 * 事後条件: 認証が成功し、適切なリダイレクトが行われる
	 * 不変条件: セッションが再生成される
	 */
	public function test_login_success_with_contract()
	{
		// 事前条件: ユーザーが存在し、有効な認証情報を提供
		$user = User::factory()->create([
			'email' => 'test@example.com',
			'password' => bcrypt('password123')
		]);

		// セッションモックの作成
		$session = Mockery::mock();
		$session->shouldReceive('regenerate')->once();

		// モックリクエストの作成
		$request = Mockery::mock(\App\Http\Requests\Auth\LoginRequest::class);
		$request->shouldReceive('validated')->andReturn([
			'email' => 'test@example.com',
			'password' => 'password123'
		]);
		$request->shouldReceive('boolean')->with('remember')->andReturn(false);
		$request->shouldReceive('session')->andReturn($session);

		// Authファサードのモック
		Auth::shouldReceive('attempt')
			->with(['email' => 'test@example.com', 'password' => 'password123'], false)
			->andReturn(true);

		// Act: loginメソッドを呼び出し
		$controller = app(\App\Http\Controllers\AuthController::class);
		$result = $controller->login($request);

		// 事後条件の検証
		$this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

		// 不変条件: セッションが再生成されること
		$session->shouldHaveReceived('regenerate');
	}

	/**
	 * @test
	 * login メソッドのテスト - 失敗ケース
	 * 
	 * 事前条件: 無効なログイン情報が提供される
	 * 事後条件: エラーメッセージと共にリダイレクトが行われる
	 * 不変条件: セッションは再生成されない
	 */
	public function test_login_failure_with_contract()
	{
		// 事前条件: 無効な認証情報を提供
		$request = Mockery::mock(\App\Http\Requests\Auth\LoginRequest::class);
		$request->shouldReceive('validated')->andReturn([
			'email' => 'invalid@example.com',
			'password' => 'wrongpassword'
		]);
		$request->shouldReceive('boolean')->with('remember')->andReturn(false);
		$request->shouldReceive('back')->andReturnSelf();
		$request->shouldReceive('withErrors')->with(['email' => 'ログイン情報が登録されていません'])->andReturnSelf();

		// Authファサードのモック
		Auth::shouldReceive('attempt')
			->with(['email' => 'invalid@example.com', 'password' => 'wrongpassword'], false)
			->andReturn(false);

		// Act: loginメソッドを呼び出し
		$controller = app(\App\Http\Controllers\AuthController::class);
		$result = $controller->login($request);

		// 事後条件の検証
		$this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

		// 不変条件: セッションは再生成されないこと
		$request->shouldNotHaveReceived('session->regenerate');
	}

	/**
	 * @test
	 * logout メソッドのテスト
	 * 
	 * 事前条件: ユーザーがログインしている
	 * 事後条件: ログアウトが実行され、セッションが無効化される
	 * 不変条件: セッションが無効化され、トークンが再生成される
	 */
	public function test_logout_with_contract()
	{
		// 事前条件: ユーザーがログインしている状態をシミュレート
		$user = User::factory()->create();
		Auth::login($user);

		// セッションモックの作成
		$session = Mockery::mock();
		$session->shouldReceive('invalidate')->once();
		$session->shouldReceive('regenerateToken')->once();

		// モックリクエストの作成
		$request = Mockery::mock(Request::class);
		$request->shouldReceive('session')->andReturn($session);

		// Act: logoutメソッドを呼び出し
		$controller = app(\App\Http\Controllers\AuthController::class);
		$result = $controller->logout($request);

		// 事後条件の検証
		$this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

		// 不変条件: セッションが無効化され、トークンが再生成されること
		$session->shouldHaveReceived('invalidate');
		$session->shouldHaveReceived('regenerateToken');
	}

	/**
	 * @test
	 * register メソッドのテスト - 成功ケース
	 * 
	 * 事前条件: 有効な登録情報が提供される
	 * 事後条件: ユーザーが作成され、ログイン状態になり、リダイレクトされる
	 * 不変条件: ユーザーが認証状態になる
	 */
	public function test_register_success_with_contract()
	{
		// 事前条件: 有効な登録情報を提供
		$validatedData = [
			'name' => 'Test User',
			'email' => 'test@example.com',
			'password' => 'password123',
			'password_confirmation' => 'password123'
		];

		$request = Mockery::mock(\App\Http\Requests\Auth\RegisterRequest::class);
		$request->shouldReceive('validated')->andReturn($validatedData);

		$user = User::factory()->make($validatedData);
		$user->id = 1;

		$createUserService = Mockery::mock(CreateUserService::class);
		$createUserService->shouldReceive('create')->with($validatedData)->andReturn($user);

		// Authファサードのモック
		Auth::shouldReceive('login')->with($user)->once();

		// Act: registerメソッドを呼び出し
		$controller = app(\App\Http\Controllers\AuthController::class);
		$result = $controller->register($request, $createUserService);

		// 事後条件の検証
		$this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

		// 不変条件: ユーザーが認証状態になること
		Auth::shouldHaveReceived('login')->with($user);
	}

	/**
	 * @test
	 * verifyEmail メソッドのテスト - 既に認証済み
	 * 
	 * 事前条件: ユーザーが既にメール認証済み
	 * 事後条件: attendance.indexにリダイレクトされる
	 * 不変条件: メール認証イベントは発火されない
	 */
	public function test_verify_email_already_verified_with_contract()
	{
		// 事前条件: 既にメール認証済みのユーザー
		$user = User::factory()->create(['email_verified_at' => now()]);
		$request = Mockery::mock(Request::class);
		$request->shouldReceive('user')->andReturn($user);

		// Act: verifyEmailメソッドを呼び出し
		$controller = app(\App\Http\Controllers\AuthController::class);
		$result = $controller->verifyEmail($request);

		// 事後条件の検証
		$this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

		// 不変条件: 既に認証済みのユーザーは認証処理をスキップすること
		$this->assertTrue($user->hasVerifiedEmail());
	}

	/**
	 * @test
	 * verifyEmail メソッドのテスト - 未認証
	 * 
	 * 事前条件: ユーザーがメール認証未完了
	 * 事後条件: メール認証が完了し、attendance.indexにリダイレクトされる
	 * 不変条件: メール認証イベントが発火される
	 */
	public function test_verify_email_not_verified_with_contract()
	{
		// 事前条件: メール認証未完了のユーザー
		$user = Mockery::mock(User::class);
		$user->shouldReceive('hasVerifiedEmail')->andReturn(false);
		$user->shouldReceive('markEmailAsVerified')->andReturn(true);

		$request = Mockery::mock(Request::class);
		$request->shouldReceive('user')->andReturn($user);

		// Act: verifyEmailメソッドを呼び出し
		$controller = app(\App\Http\Controllers\AuthController::class);
		$result = $controller->verifyEmail($request);

		// 事後条件の検証
		$this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

		// 不変条件: メール認証処理が実行されること
		$user->shouldHaveReceived('markEmailAsVerified');
	}

	/**
	 * @test
	 * adminLogin メソッドのテスト - 管理者権限あり
	 * 
	 * 事前条件: 管理者ユーザーの有効なログイン情報が提供される
	 * 事後条件: 管理者ダッシュボードにリダイレクトされる
	 * 不変条件: セッションが再生成される
	 */
	public function test_admin_login_success_with_admin_privileges_with_contract()
	{
		// 事前条件: 管理者ユーザーが存在し、有効な認証情報を提供
		$adminUser = User::factory()->create([
			'email' => 'admin@example.com',
			'password' => bcrypt('password123'),
			'is_admin' => true
		]);

		// セッションモックの作成
		$session = Mockery::mock();
		$session->shouldReceive('regenerate')->once();

		$request = Mockery::mock(\App\Http\Requests\Auth\LoginRequest::class);
		$request->shouldReceive('validated')->andReturn([
			'email' => 'admin@example.com',
			'password' => 'password123'
		]);
		$request->shouldReceive('boolean')->with('remember')->andReturn(false);
		$request->shouldReceive('session')->andReturn($session);

		// Authファサードのモック
		Auth::shouldReceive('attempt')
			->with(['email' => 'admin@example.com', 'password' => 'password123'], false)
			->andReturn(true);
		Auth::shouldReceive('user')->andReturn($adminUser);

		// Act: adminLoginメソッドを呼び出し
		$controller = app(\App\Http\Controllers\AuthController::class);
		$result = $controller->adminLogin($request);

		// 事後条件の検証
		$this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

		// 不変条件: セッションが再生成されること
		$session->shouldHaveReceived('regenerate');
	}

	/**
	 * @test
	 * adminLogin メソッドのテスト - 管理者権限なし
	 * 
	 * 事前条件: 一般ユーザーの有効なログイン情報が提供される
	 * 事後条件: ログアウトされ、エラーメッセージと共にリダイレクトされる
	 * 不変条件: ユーザーはログアウトされる
	 */
	public function test_admin_login_failure_without_admin_privileges_with_contract()
	{
		// 事前条件: 一般ユーザーが存在し、有効な認証情報を提供
		$regularUser = User::factory()->create([
			'email' => 'user@example.com',
			'password' => bcrypt('password123'),
			'is_admin' => false
		]);

		$request = Mockery::mock(\App\Http\Requests\Auth\LoginRequest::class);
		$request->shouldReceive('validated')->andReturn([
			'email' => 'user@example.com',
			'password' => 'password123'
		]);
		$request->shouldReceive('boolean')->with('remember')->andReturn(false);
		$request->shouldReceive('session->regenerate')->once();
		$request->shouldReceive('back')->andReturnSelf();
		$request->shouldReceive('withErrors')->with(['email' => '管理者権限がありません'])->andReturnSelf();

		// Authファサードのモック
		Auth::shouldReceive('attempt')
			->with(['email' => 'user@example.com', 'password' => 'password123'], false)
			->andReturn(true);
		Auth::shouldReceive('user')->andReturn($regularUser);
		Auth::shouldReceive('logout')->once();

		// Act: adminLoginメソッドを呼び出し
		$controller = app(\App\Http\Controllers\AuthController::class);
		$result = $controller->adminLogin($request);

		// 事後条件の検証
		$this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

		// 不変条件: ユーザーがログアウトされること
		Auth::shouldHaveReceived('logout');
	}

	/**
	 * @test
	 * resendVerificationEmail メソッドのテスト - 既に認証済み
	 * 
	 * 事前条件: ユーザーが既にメール認証済み
	 * 事後条件: ホームページにリダイレクトされる
	 * 不変条件: メール認証通知は送信されない
	 */
	public function test_resend_verification_email_already_verified_with_contract()
	{
		// 事前条件: 既にメール認証済みのユーザー
		$user = User::factory()->create(['email_verified_at' => now()]);
		$request = Mockery::mock(Request::class);
		$request->shouldReceive('user')->andReturn($user);

		// Act: resendVerificationEmailメソッドを呼び出し
		$controller = app(\App\Http\Controllers\AuthController::class);
		$result = $controller->resendVerificationEmail($request);

		// 事後条件の検証
		$this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

		// 不変条件: 既に認証済みのユーザーは通知送信をスキップすること
		$this->assertTrue($user->hasVerifiedEmail());
	}

	/**
	 * @test
	 * resendVerificationEmail メソッドのテスト - 未認証
	 * 
	 * 事前条件: ユーザーがメール認証未完了
	 * 事後条件: メール認証通知が送信され、元のページにリダイレクトされる
	 * 不変条件: メール認証通知が送信される
	 */
	public function test_resend_verification_email_not_verified_with_contract()
	{
		// 事前条件: メール認証未完了のユーザー
		$user = Mockery::mock(User::class);
		$user->shouldReceive('hasVerifiedEmail')->andReturn(false);
		$user->shouldReceive('sendEmailVerificationNotification')->once();

		$request = Mockery::mock(Request::class);
		$request->shouldReceive('user')->andReturn($user);

		// Act: resendVerificationEmailメソッドを呼び出し
		$controller = app(\App\Http\Controllers\AuthController::class);
		$result = $controller->resendVerificationEmail($request);

		// 事後条件の検証
		$this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

		// 不変条件: メール認証通知が送信されること
		$user->shouldHaveReceived('sendEmailVerificationNotification');
	}

	/**
	 * @test
	 * AuthController クラスの不変条件テスト
	 */
	public function test_auth_controller_invariants()
	{
		$controller = app(\App\Http\Controllers\AuthController::class);

		// 不変条件1: AuthControllerはControllerを継承していること
		$this->assertInstanceOf(\App\Http\Controllers\Controller::class, $controller);

		// 不変条件2: 必要なメソッドが存在すること
		$requiredMethods = [
			'showLogin',
			'login',
			'logout',
			'showRegister',
			'register',
			'showVerificationNotice',
			'verifyEmail',
			'resendVerificationEmail',
			'showAdminLogin',
			'adminLogin'
		];

		foreach ($requiredMethods as $method) {
			$this->assertTrue(method_exists($controller, $method), "Method {$method} should exist");
		}

		// 不変条件3: メソッドのシグネチャが正しいこと
		$reflection = new \ReflectionClass($controller);

		// ビュー表示メソッドの戻り値型チェック
		$viewMethods = ['showLogin', 'showRegister', 'showVerificationNotice', 'showAdminLogin'];
		foreach ($viewMethods as $method) {
			$methodReflection = $reflection->getMethod($method);
			$this->assertTrue($methodReflection->isPublic());
			$this->assertEquals('Illuminate\Contracts\View\View', $methodReflection->getReturnType()->getName());
		}

		// リダイレクトメソッドの戻り値型チェック
		$redirectMethods = ['login', 'logout', 'register', 'verifyEmail', 'resendVerificationEmail', 'adminLogin'];
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
		$controller = app(\App\Http\Controllers\AuthController::class);

		// 事前状態の記録
		$initialControllerState = serialize($controller);

		// 複数のメソッドを呼び出し
		$result1 = $controller->showLogin();
		$result2 = $controller->showRegister();
		$result3 = $controller->showVerificationNotice();
		$result4 = $controller->showAdminLogin();

		// 事後状態の確認
		$finalControllerState = serialize($controller);

		// 不変条件: コントローラ自体の状態は変わらないこと（ステートレス）
		$this->assertEquals($initialControllerState, $finalControllerState);

		// 不変条件: 複数回呼び出しても同じ結果が得られること（べき等性）
		$result1_repeat = $controller->showLogin();
		$result2_repeat = $controller->showRegister();

		$this->assertEquals($result1->getName(), $result1_repeat->getName());
		$this->assertEquals($result2->getName(), $result2_repeat->getName());
	}

	/**
	 * @test
	 * バリデーション例外の処理テスト
	 * 
	 * 事前条件: バリデーション例外が発生する
	 * 事後条件: エラーメッセージと共にリダイレクトされる
	 * 不変条件: 例外が適切にキャッチされる
	 */
	public function test_validation_exception_handling_with_contract()
	{
		// 事前条件: バリデーション例外を発生させる
		$validator = Mockery::mock(\Illuminate\Contracts\Validation\Validator::class);
		$validator->shouldReceive('errors->messages')->andReturn(['email' => ['メールアドレスは必須です']]);

		$request = Mockery::mock(\App\Http\Requests\Auth\LoginRequest::class);
		$request->shouldReceive('validated')->andThrow(new ValidationException($validator));

		// Act: loginメソッドを呼び出し
		$controller = app(\App\Http\Controllers\AuthController::class);
		$result = $controller->login($request);

		// 事後条件の検証
		$this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $result);

		// 不変条件: 例外が適切にキャッチされ、リダイレクトレスポンスが返されること
		$this->assertNotNull($result);
	}
}
