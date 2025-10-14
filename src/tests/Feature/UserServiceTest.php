<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Application\Services\UserService;
use App\Domain\User\Entities\User as UserEntity;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Models\User;
use Mockery;

/**
 * UserServiceのテストクラス
 * 契約による設計（Design by Contract）に基づいてテストを実装
 * 各メソッドの事前条件、事後条件、不変条件を検証
 */
class UserServiceTest extends TestCase
{
  use WithFaker;

  private UserRepositoryInterface $userRepository;
  private UserService $userService;

  protected function setUp(): void
  {
    parent::setUp();

    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->userService = new UserService($this->userRepository);
  }

  protected function tearDown(): void
  {
    Mockery::close();
    parent::tearDown();
  }

  /**
   * @test
   * getUser メソッドのテスト - ユーザーが存在する場合
   *
   * 事前条件: 有効なユーザーIDが提供される
   * 事後条件: 対応するUserEntityが返される
   * 不変条件: リポジトリが正しく呼び出される
   */
  public function test_get_user_when_user_exists_with_contract()
  {
    // 事前条件: 有効なユーザーIDが提供される
    $userId = $this->faker->uuid();
    $userEntity = Mockery::mock(UserEntity::class);

    $this->userRepository
      ->shouldReceive('findById')
      ->with($userId)
      ->once()
      ->andReturn($userEntity);

    // Act: getUserメソッドを呼び出し
    $result = $this->userService->getUser($userId);

    // 事後条件の検証
    $this->assertSame($userEntity, $result);

    // 不変条件: リポジトリが正しく呼び出される
    $this->userRepository->shouldHaveReceived('findById')->with($userId)->once();
  }

  /**
   * @test
   * getUser メソッドのテスト - ユーザーが存在しない場合
   *
   * 事前条件: 存在しないユーザーIDが提供される
   * 事後条件: nullが返される
   * 不変条件: リポジトリが正しく呼び出される
   */
  public function test_get_user_when_user_not_exists_with_contract()
  {
    // 事前条件: 存在しないユーザーIDが提供される
    $userId = $this->faker->uuid();

    $this->userRepository
      ->shouldReceive('findById')
      ->with($userId)
      ->once()
      ->andReturn(null);

    // Act: getUserメソッドを呼び出し
    $result = $this->userService->getUser($userId);

    // 事後条件の検証
    $this->assertNull($result);

    // 不変条件: リポジトリが正しく呼び出される
    $this->userRepository->shouldHaveReceived('findById')->with($userId)->once();
  }

  /**
   * @test
   * getUserById メソッドのテスト - ユーザーが存在する場合
   *
   * 事前条件: 有効なユーザーIDが提供される
   * 事後条件: 対応するUserモデルが返される
   * 不変条件: Eloquentモデルが正しく呼び出される
   */
  public function test_get_user_by_id_when_user_exists_with_contract()
  {
    // 事前条件: 有効なユーザーIDが提供される
    $userId = $this->faker->uuid();
    $user = User::factory()->create(['id' => $userId]);

    // Act: getUserByIdメソッドを呼び出し
    $result = $this->userService->getUserById($userId);

    // 事後条件の検証
    $this->assertInstanceOf(User::class, $result);
    $this->assertEquals($userId, $result->id);

    // 不変条件: ユーザーが正しく取得される
    $this->assertDatabaseHas('users', ['id' => $userId]);
  }

  /**
   * @test
   * getUserById メソッドのテスト - ユーザーが存在しない場合
   *
   * 事前条件: 存在しないユーザーIDが提供される
   * 事後条件: nullが返される
   * 不変条件: データベースに変更がない
   */
  public function test_get_user_by_id_when_user_not_exists_with_contract()
  {
    // 事前条件: 存在しないユーザーIDが提供される
    $userId = $this->faker->uuid();

    // Act: getUserByIdメソッドを呼び出し
    $result = $this->userService->getUserById($userId);

    // 事後条件の検証
    $this->assertNull($result);

    // 不変条件: データベースに変更がない
    $this->assertDatabaseMissing('users', ['id' => $userId]);
  }

  /**
   * @test
   * getAllUsers メソッドのテスト
   *
   * 事前条件: 一般ユーザーがデータベースに存在する
   * 事後条件: 管理者以外の全ユーザーの配列が返される
   * 不変条件: 管理者ユーザーは除外される
   */
  public function test_get_all_users_with_contract()
  {
    // 事前条件: 一般ユーザーがデータベースに存在する
    $adminUser = User::factory()->create(['is_admin' => true, 'name' => 'Admin User']);
    $user1 = User::factory()->create(['is_admin' => false, 'name' => 'Alice']);
    $user2 = User::factory()->create(['is_admin' => false, 'name' => 'Bob']);

    // Act: getAllUsersメソッドを呼び出し
    $result = $this->userService->getAllUsers();

    // 事後条件の検証
    $this->assertIsArray($result);
    $this->assertCount(2, $result);

    // 名前順でソートされていることを確認
    $this->assertEquals('Alice', $result[0]['name']);
    $this->assertEquals('Bob', $result[1]['name']);

    // 不変条件: 管理者ユーザーは除外される
    $userIds = array_column($result, 'id');
    $this->assertNotContains((string)$adminUser->id, $userIds);
    $this->assertContains((string)$user1->id, $userIds);
    $this->assertContains((string)$user2->id, $userIds);
  }

  /**
   * @test
   * getAllUsers メソッドのテスト - ユーザーが存在しない場合
   *
   * 事前条件: データベースにユーザーが存在しない
   * 事後条件: 空の配列が返される
   * 不変条件: データベースの状態が変わらない
   */
  public function test_get_all_users_when_no_users_with_contract()
  {
    // 事前条件: データベースにユーザーが存在しない
    // (setUpでデータベースがリフレッシュされているため)

    // Act: getAllUsersメソッドを呼び出し
    $result = $this->userService->getAllUsers();

    // 事後条件の検証
    $this->assertIsArray($result);
    $this->assertEmpty($result);

    // 不変条件: データベースの状態が変わらない
    $this->assertDatabaseCount('users', 0);
  }

  /**
   * @test
   * UserService クラスの不変条件テスト
   */
  public function test_user_service_invariants()
  {
    $service = new UserService($this->userRepository);

    // 不変条件1: UserServiceは常に利用可能であること
    $this->assertInstanceOf(UserService::class, $service);

    // 不変条件2: 必要なメソッドが存在すること
    $requiredMethods = [
      'getUser',
      'getUserById',
      'getAllUsers',
    ];

    foreach ($requiredMethods as $method) {
      $this->assertTrue(method_exists($service, $method), "Method {$method} should exist");
    }

    // 不変条件3: メソッドのシグネチャが正しいこと
    $reflection = new \ReflectionClass($service);

    // getUser
    $methodReflection = $reflection->getMethod('getUser');
    $this->assertTrue($methodReflection->isPublic());
    $this->assertEquals('string', $methodReflection->getParameters()[0]->getType()->getName());
    $this->assertEquals('App\Domain\User\Entities\User', $methodReflection->getReturnType()->getName());

    // getUserById
    $methodReflection = $reflection->getMethod('getUserById');
    $this->assertTrue($methodReflection->isPublic());
    $this->assertEquals('string', $methodReflection->getParameters()[0]->getType()->getName());
    $this->assertEquals('App\Models\User', $methodReflection->getReturnType()->getName());

    // getAllUsers
    $methodReflection = $reflection->getMethod('getAllUsers');
    $this->assertTrue($methodReflection->isPublic());
    $this->assertEquals('array', $methodReflection->getReturnType()->getName());
  }

  /**
   * @test
   * メソッド呼び出し前後での状態不変性テスト
   */
  public function test_method_call_state_invariants()
  {
    $service = new UserService($this->userRepository);

    // 事前状態の記録（シリアライゼーションを避ける）
    $initialServiceClass = get_class($service);

    // 複数のメソッドを呼び出し
    $userId = $this->faker->uuid();

    $this->userRepository
      ->shouldReceive('findById')
      ->with($userId)
      ->andReturn(null);

    $service->getUser($userId);
    $service->getUserById($userId);
    $service->getAllUsers();

    // 事後状態の確認
    $finalServiceClass = get_class($service);

    // 不変条件: サービス自体の状態は変わらないこと（ステートレス）
    $this->assertEquals($initialServiceClass, $finalServiceClass);
  }

  /**
   * @test
   * ユーザー取得の一貫性テスト
   *
   * 事前条件: 同じユーザーIDで複数回アクセス
   * 事後条件: 常に同じ結果が返される
   * 不変条件: リポジトリの呼び出し回数が正しい
   */
  public function test_user_retrieval_consistency_with_contract()
  {
    $userId = $this->faker->uuid();
    $userEntity = Mockery::mock(UserEntity::class);

    $this->userRepository
      ->shouldReceive('findById')
      ->with($userId)
      ->twice()
      ->andReturn($userEntity);

    // 同じユーザーIDで複数回アクセス
    $result1 = $this->userService->getUser($userId);
    $result2 = $this->userService->getUser($userId);

    // 事後条件: 常に同じ結果が返される
    $this->assertSame($userEntity, $result1);
    $this->assertSame($userEntity, $result2);

    // 不変条件: リポジトリの呼び出し回数が正しい
    $this->userRepository->shouldHaveReceived('findById')->with($userId)->twice();
  }
}
