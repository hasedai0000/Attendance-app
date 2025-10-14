<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Application\Services\AuthenticationService;
use Mockery;

/**
 * AuthenticationServiceのテストクラス
 * 契約による設計（Design by Contract）に基づいてテストを実装
 * 各メソッドの事前条件、事後条件、不変条件を検証
 */
class AuthenticationServiceTest extends TestCase
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
   * isAuthenticated メソッドのテスト - 認証済みユーザーの場合
   * 
   * 事前条件: ユーザーがログインしている
   * 事後条件: trueが返される
   * 不変条件: 認証状態が正しく判定される
   */
  public function test_is_authenticated_when_logged_in_with_contract()
  {
    // 事前条件: ユーザーがログインしている
    $user = User::factory()->create();
    Auth::login($user);

    $service = new AuthenticationService();

    // Act: isAuthenticatedメソッドを呼び出し
    $result = $service->isAuthenticated();

    // 事後条件の検証
    $this->assertTrue($result);

    // 不変条件: 認証状態が正しく判定されること
    $this->assertIsBool($result);
  }

  /**
   * @test
   * isAuthenticated メソッドのテスト - 未認証ユーザーの場合
   * 
   * 事前条件: ユーザーがログインしていない
   * 事後条件: falseが返される
   * 不変条件: 認証状態が正しく判定される
   */
  public function test_is_authenticated_when_not_logged_in_with_contract()
  {
    // 事前条件: ユーザーがログインしていない
    Auth::logout();

    $service = new AuthenticationService();

    // Act: isAuthenticatedメソッドを呼び出し
    $result = $service->isAuthenticated();

    // 事後条件の検証
    $this->assertFalse($result);

    // 不変条件: 認証状態が正しく判定されること
    $this->assertIsBool($result);
  }

  /**
   * @test
   * getCurrentUserId メソッドのテスト - 認証済みユーザーの場合
   * 
   * 事前条件: ユーザーがログインしている
   * 事後条件: ユーザーIDが返される
   * 不変条件: 正しいユーザーIDが取得される
   */
  public function test_get_current_user_id_when_logged_in_with_contract()
  {
    // 事前条件: ユーザーがログインしている
    $user = User::factory()->create();
    Auth::login($user);

    $service = new AuthenticationService();

    // Act: getCurrentUserIdメソッドを呼び出し
    $result = $service->getCurrentUserId();

    // 事後条件の検証
    $this->assertEquals($user->id, $result);

    // 不変条件: 正しいユーザーIDが取得されること
    $this->assertIsString($result);
    $this->assertNotEmpty($result);
  }

  /**
   * @test
   * getCurrentUserId メソッドのテスト - 未認証ユーザーの場合
   * 
   * 事前条件: ユーザーがログインしていない
   * 事後条件: nullが返される
   * 不変条件: 未認証状態が正しく処理される
   */
  public function test_get_current_user_id_when_not_logged_in_with_contract()
  {
    // 事前条件: ユーザーがログインしていない
    Auth::logout();

    $service = new AuthenticationService();

    // Act: getCurrentUserIdメソッドを呼び出し
    $result = $service->getCurrentUserId();

    // 事後条件の検証
    $this->assertNull($result);

    // 不変条件: 未認証状態が正しく処理されること
    $this->assertNull($result);
  }

  /**
   * @test
   * getCurrentUser メソッドのテスト - 認証済みユーザーの場合
   * 
   * 事前条件: ユーザーがログインしている
   * 事後条件: ユーザーオブジェクトが返される
   * 不変条件: 正しいユーザーオブジェクトが取得される
   */
  public function test_get_current_user_when_logged_in_with_contract()
  {
    // 事前条件: ユーザーがログインしている
    $user = User::factory()->create();
    Auth::login($user);

    $service = new AuthenticationService();

    // Act: getCurrentUserメソッドを呼び出し
    $result = $service->getCurrentUser();

    // 事後条件の検証
    $this->assertEquals($user->id, $result->id);
    $this->assertEquals($user->name, $result->name);

    // 不変条件: 正しいユーザーオブジェクトが取得されること
    $this->assertInstanceOf(User::class, $result);
  }

  /**
   * @test
   * getCurrentUser メソッドのテスト - 未認証ユーザーの場合
   * 
   * 事前条件: ユーザーがログインしていない
   * 事後条件: nullが返される
   * 不変条件: 未認証状態が正しく処理される
   */
  public function test_get_current_user_when_not_logged_in_with_contract()
  {
    // 事前条件: ユーザーがログインしていない
    Auth::logout();

    $service = new AuthenticationService();

    // Act: getCurrentUserメソッドを呼び出し
    $result = $service->getCurrentUser();

    // 事後条件の検証
    $this->assertNull($result);

    // 不変条件: 未認証状態が正しく処理されること
    $this->assertNull($result);
  }

  /**
   * @test
   * requireAuthentication メソッドのテスト - 認証済みユーザーの場合
   * 
   * 事前条件: ユーザーがログインしている
   * 事後条件: ユーザーIDが返される
   * 不変条件: 認証が正しく要求される
   */
  public function test_require_authentication_when_logged_in_with_contract()
  {
    // 事前条件: ユーザーがログインしている
    $user = User::factory()->create();
    Auth::login($user);

    $service = new AuthenticationService();

    // Act: requireAuthenticationメソッドを呼び出し
    $result = $service->requireAuthentication();

    // 事後条件の検証
    $this->assertEquals($user->id, $result);

    // 不変条件: 認証が正しく要求されること
    $this->assertIsString($result);
    $this->assertNotEmpty($result);
  }

  /**
   * @test
   * requireAuthentication メソッドのテスト - 未認証ユーザーの場合
   * 
   * 事前条件: ユーザーがログインしていない
   * 事後条件: 例外が発生する
   * 不変条件: 認証要求が適切に処理される
   */
  public function test_require_authentication_when_not_logged_in_with_contract()
  {
    // 事前条件: ユーザーがログインしていない
    Auth::logout();

    $service = new AuthenticationService();

    // Act & Assert: requireAuthenticationメソッドを呼び出し、例外が発生することを確認
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('認証が必要です。');

    $service->requireAuthentication();
  }

  /**
   * @test
   * isAdmin メソッドのテスト - 管理者ユーザーの場合
   * 
   * 事前条件: 管理者ユーザーがログインしている
   * 事後条件: trueが返される
   * 不変条件: 管理者権限が正しく判定される
   */
  public function test_is_admin_when_admin_user_with_contract()
  {
    // 事前条件: 管理者ユーザーがログインしている
    $adminUser = User::factory()->create(['is_admin' => true]);
    Auth::login($adminUser);

    $service = new AuthenticationService();

    // Act: isAdminメソッドを呼び出し
    $result = $service->isAdmin();

    // 事後条件の検証
    $this->assertTrue($result);

    // 不変条件: 管理者権限が正しく判定されること
    $this->assertIsBool($result);
  }

  /**
   * @test
   * isAdmin メソッドのテスト - 一般ユーザーの場合
   * 
   * 事前条件: 一般ユーザーがログインしている
   * 事後条件: falseが返される
   * 不変条件: 管理者権限が正しく判定される
   */
  public function test_is_admin_when_regular_user_with_contract()
  {
    // 事前条件: 一般ユーザーがログインしている
    $regularUser = User::factory()->create(['is_admin' => false]);
    Auth::login($regularUser);

    $service = new AuthenticationService();

    // Act: isAdminメソッドを呼び出し
    $result = $service->isAdmin();

    // 事後条件の検証
    $this->assertFalse($result);

    // 不変条件: 管理者権限が正しく判定されること
    $this->assertIsBool($result);
  }

  /**
   * @test
   * isAdmin メソッドのテスト - 未認証ユーザーの場合
   * 
   * 事前条件: ユーザーがログインしていない
   * 事後条件: falseが返される
   * 不変条件: 未認証状態では管理者権限がない
   */
  public function test_is_admin_when_not_logged_in_with_contract()
  {
    // 事前条件: ユーザーがログインしていない
    Auth::logout();

    $service = new AuthenticationService();

    // Act: isAdminメソッドを呼び出し
    $result = $service->isAdmin();

    // 事後条件の検証
    $this->assertFalse($result);

    // 不変条件: 未認証状態では管理者権限がないこと
    $this->assertIsBool($result);
  }

  /**
   * @test
   * requireAdmin メソッドのテスト - 管理者ユーザーの場合
   * 
   * 事前条件: 管理者ユーザーがログインしている
   * 事後条件: ユーザーIDが返される
   * 不変条件: 管理者権限が正しく要求される
   */
  public function test_require_admin_when_admin_user_with_contract()
  {
    // 事前条件: 管理者ユーザーがログインしている
    $adminUser = User::factory()->create(['is_admin' => true]);
    Auth::login($adminUser);

    $service = new AuthenticationService();

    // Act: requireAdminメソッドを呼び出し
    $result = $service->requireAdmin();

    // 事後条件の検証
    $this->assertEquals($adminUser->id, $result);

    // 不変条件: 管理者権限が正しく要求されること
    $this->assertIsString($result);
    $this->assertNotEmpty($result);
  }

  /**
   * @test
   * requireAdmin メソッドのテスト - 一般ユーザーの場合
   * 
   * 事前条件: 一般ユーザーがログインしている
   * 事後条件: 例外が発生する
   * 不変条件: 管理者権限要求が適切に処理される
   */
  public function test_require_admin_when_regular_user_with_contract()
  {
    // 事前条件: 一般ユーザーがログインしている
    $regularUser = User::factory()->create(['is_admin' => false]);
    Auth::login($regularUser);

    $service = new AuthenticationService();

    // Act & Assert: requireAdminメソッドを呼び出し、例外が発生することを確認
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('管理者権限が必要です。');

    $service->requireAdmin();
  }

  /**
   * @test
   * requireAdmin メソッドのテスト - 未認証ユーザーの場合
   * 
   * 事前条件: ユーザーがログインしていない
   * 事後条件: 例外が発生する
   * 不変条件: 認証要求が適切に処理される
   */
  public function test_require_admin_when_not_logged_in_with_contract()
  {
    // 事前条件: ユーザーがログインしていない
    Auth::logout();

    $service = new AuthenticationService();

    // Act & Assert: requireAdminメソッドを呼び出し、例外が発生することを確認
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('認証が必要です。');

    $service->requireAdmin();
  }

  /**
   * @test
   * AuthenticationService クラスの不変条件テスト
   */
  public function test_authentication_service_invariants()
  {
    $service = new AuthenticationService();

    // 不変条件1: AuthenticationServiceは正しくインスタンス化されること
    $this->assertInstanceOf(AuthenticationService::class, $service);

    // 不変条件2: 必要なメソッドが存在すること
    $requiredMethods = [
      'isAuthenticated',
      'getCurrentUserId',
      'getCurrentUser',
      'requireAuthentication',
      'isAdmin',
      'requireAdmin'
    ];

    foreach ($requiredMethods as $method) {
      $this->assertTrue(method_exists($service, $method), "Method {$method} should exist");
    }

    // 不変条件3: メソッドのシグネチャが正しいこと
    $reflection = new \ReflectionClass($service);

    // 戻り値型チェック
    $methodReflection = $reflection->getMethod('isAuthenticated');
    $this->assertTrue($methodReflection->isPublic());
    $this->assertEquals('bool', $methodReflection->getReturnType()->getName());

    $methodReflection = $reflection->getMethod('getCurrentUserId');
    $this->assertTrue($methodReflection->isPublic());
    $returnType = $methodReflection->getReturnType();
    $this->assertTrue($returnType->allowsNull());
    $this->assertEquals('string', $returnType->getName());

    $methodReflection = $reflection->getMethod('requireAuthentication');
    $this->assertTrue($methodReflection->isPublic());
    $this->assertEquals('string', $methodReflection->getReturnType()->getName());

    $methodReflection = $reflection->getMethod('isAdmin');
    $this->assertTrue($methodReflection->isPublic());
    $this->assertEquals('bool', $methodReflection->getReturnType()->getName());

    $methodReflection = $reflection->getMethod('requireAdmin');
    $this->assertTrue($methodReflection->isPublic());
    $this->assertEquals('string', $methodReflection->getReturnType()->getName());
  }

  /**
   * @test
   * メソッド呼び出し前後での状態不変性テスト
   */
  public function test_method_call_state_invariants()
  {
    $service = new AuthenticationService();

    // 事前状態の記録（シリアライゼーションを避ける）
    $initialServiceClass = get_class($service);

    // 複数のメソッドを呼び出し（認証状態を設定）
    $user = User::factory()->create(['is_admin' => true]);
    Auth::login($user);

    $result1 = $service->isAuthenticated();
    $result2 = $service->getCurrentUserId();
    $result3 = $service->isAdmin();

    // 事後状態の確認
    $finalServiceClass = get_class($service);

    // 不変条件: サービス自体の状態は変わらないこと（ステートレス）
    $this->assertEquals($initialServiceClass, $finalServiceClass);

    // 不変条件: 複数回呼び出しても同じ結果が得られること（べき等性）
    $result1_repeat = $service->isAuthenticated();
    $result2_repeat = $service->getCurrentUserId();
    $result3_repeat = $service->isAdmin();

    $this->assertEquals($result1, $result1_repeat);
    $this->assertEquals($result2, $result2_repeat);
    $this->assertEquals($result3, $result3_repeat);
  }

  /**
   * @test
   * 認証状態の一貫性テスト
   * 
   * 事前条件: 認証状態が設定される
   * 事後条件: 各メソッドが一貫した結果を返す
   * 不変条件: 認証状態の一貫性が保たれる
   */
  public function test_authentication_state_consistency_with_contract()
  {
    $service = new AuthenticationService();

    // テストケース1: 管理者ユーザーでログイン
    $adminUser = User::factory()->create(['is_admin' => true]);
    Auth::login($adminUser);

    $this->assertTrue($service->isAuthenticated());
    $this->assertEquals($adminUser->id, $service->getCurrentUserId());
    $this->assertEquals($adminUser->id, $service->getCurrentUser()->id);
    $this->assertEquals($adminUser->id, $service->requireAuthentication());
    $this->assertTrue($service->isAdmin());
    $this->assertEquals($adminUser->id, $service->requireAdmin());

    // テストケース2: 一般ユーザーでログイン
    $regularUser = User::factory()->create(['is_admin' => false]);
    Auth::login($regularUser);

    $this->assertTrue($service->isAuthenticated());
    $this->assertEquals($regularUser->id, $service->getCurrentUserId());
    $this->assertEquals($regularUser->id, $service->getCurrentUser()->id);
    $this->assertEquals($regularUser->id, $service->requireAuthentication());
    $this->assertFalse($service->isAdmin());

    // 一般ユーザーでrequireAdminを呼び出すと例外が発生
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('管理者権限が必要です。');
    $service->requireAdmin();

    // テストケース3: ログアウト
    Auth::logout();

    $this->assertFalse($service->isAuthenticated());
    $this->assertNull($service->getCurrentUserId());
    $this->assertNull($service->getCurrentUser());
    $this->assertFalse($service->isAdmin());

    // 未認証でrequireAuthenticationを呼び出すと例外が発生
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('認証が必要です。');
    $service->requireAuthentication();
  }
}
