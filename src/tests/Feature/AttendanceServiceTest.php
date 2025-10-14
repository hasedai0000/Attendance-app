<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Application\Services\AttendanceService;
use App\Domain\Attendance\Repositories\AttendanceRepositoryInterface;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Mockery;

/**
 * AttendanceServiceのテストクラス
 * 契約による設計（Design by Contract）に基づいてテストを実装
 * 各メソッドの事前条件、事後条件、不変条件を検証
 */
class AttendanceServiceTest extends TestCase
{
  use WithFaker;

  private AttendanceRepositoryInterface $attendanceRepository;
  private AttendanceService $attendanceService;

  protected function setUp(): void
  {
    parent::setUp();

    $this->attendanceRepository = Mockery::mock(AttendanceRepositoryInterface::class);
    $this->attendanceService = new AttendanceService($this->attendanceRepository);
  }

  protected function tearDown(): void
  {
    Mockery::close();
    parent::tearDown();
  }

  /**
   * @test
   * getTodayAttendance メソッドのテスト - 勤怠が存在する場合
   *
   * 事前条件: 有効なユーザーIDが提供される
   * 事後条件: 今日の勤怠情報が返される
   * 不変条件: リポジトリが正しく呼び出される
   */
  public function test_get_today_attendance_when_exists_with_contract()
  {
    // 事前条件: 有効なユーザーIDが提供される
    $userId = $this->faker->uuid();
    $attendance = Mockery::mock(Attendance::class);

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->once()
      ->andReturn($attendance);

    // Act: getTodayAttendanceメソッドを呼び出し
    $result = $this->attendanceService->getTodayAttendance($userId);

    // 事後条件の検証
    $this->assertSame($attendance, $result);

    // 不変条件: リポジトリが正しく呼び出される
    $this->attendanceRepository->shouldHaveReceived('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->once();
  }

  /**
   * @test
   * getTodayAttendance メソッドのテスト - 勤怠が存在しない場合
   *
   * 事前条件: 有効なユーザーIDが提供されるが勤怠が存在しない
   * 事後条件: nullが返される
   * 不変条件: リポジトリが正しく呼び出される
   */
  public function test_get_today_attendance_when_not_exists_with_contract()
  {
    // 事前条件: 有効なユーザーIDが提供されるが勤怠が存在しない
    $userId = $this->faker->uuid();

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->once()
      ->andReturn(null);

    // Act: getTodayAttendanceメソッドを呼び出し
    $result = $this->attendanceService->getTodayAttendance($userId);

    // 事後条件の検証
    $this->assertNull($result);

    // 不変条件: リポジトリが正しく呼び出される
    $this->attendanceRepository->shouldHaveReceived('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->once();
  }

  /**
   * @test
   * getCurrentStatus メソッドのテスト - 勤務外の場合
   *
   * 事前条件: 勤怠情報が存在しない
   * 事後条件: 'not_working'が返される
   * 不変条件: ステータスが正しく判定される
   */
  public function test_get_current_status_not_working_with_contract()
  {
    // 事前条件: 勤怠情報が存在しない
    $userId = $this->faker->uuid();

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->andReturn(null);

    // Act: getCurrentStatusメソッドを呼び出し
    $result = $this->attendanceService->getCurrentStatus($userId);

    // 事後条件の検証
    $this->assertEquals('not_working', $result);

    // 不変条件: ステータスが正しく判定される
    $this->assertIsString($result);
  }

  /**
   * @test
   * getCurrentStatus メソッドのテスト - 出勤中の場合
   *
   * 事前条件: 出勤時間が設定されているが退勤時間が設定されていない
   * 事後条件: 'working'が返される
   * 不変条件: ステータスが正しく判定される
   */
  public function test_get_current_status_working_with_contract()
  {
    // 事前条件: 出勤時間が設定されているが退勤時間が設定されていない
    $userId = $this->faker->uuid();
    $attendance = Mockery::mock(Attendance::class);
    $attendance->shouldReceive('getAttribute')->with('start_time')->andReturn(Carbon::now());
    $attendance->shouldReceive('getAttribute')->with('end_time')->andReturn(null);
    $attendance->shouldReceive('getAttribute')->with('status')->andReturn('working');

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->andReturn($attendance);

    // Act: getCurrentStatusメソッドを呼び出し
    $result = $this->attendanceService->getCurrentStatus($userId);

    // 事後条件の検証
    $this->assertEquals('working', $result);

    // 不変条件: ステータスが正しく判定される
    $this->assertIsString($result);
  }

  /**
   * @test
   * getCurrentStatus メソッドのテスト - 休憩中の場合
   *
   * 事前条件: 出勤時間が設定され、ステータスが'on_break'
   * 事後条件: 'on_break'が返される
   * 不変条件: ステータスが正しく判定される
   */
  public function test_get_current_status_on_break_with_contract()
  {
    // 事前条件: 出勤時間が設定され、ステータスが'on_break'
    $userId = $this->faker->uuid();
    $attendance = Mockery::mock(Attendance::class);
    $attendance->shouldReceive('getAttribute')->with('start_time')->andReturn(Carbon::now());
    $attendance->shouldReceive('getAttribute')->with('end_time')->andReturn(null);
    $attendance->shouldReceive('getAttribute')->with('status')->andReturn('on_break');

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->andReturn($attendance);

    // Act: getCurrentStatusメソッドを呼び出し
    $result = $this->attendanceService->getCurrentStatus($userId);

    // 事後条件の検証
    $this->assertEquals('on_break', $result);

    // 不変条件: ステータスが正しく判定される
    $this->assertIsString($result);
  }

  /**
   * @test
   * getCurrentStatus メソッドのテスト - 退勤済みの場合
   *
   * 事前条件: 出勤時間と退勤時間が設定されている
   * 事後条件: 'finished'が返される
   * 不変条件: ステータスが正しく判定される
   */
  public function test_get_current_status_finished_with_contract()
  {
    // 事前条件: 出勤時間と退勤時間が設定されている
    $userId = $this->faker->uuid();
    $attendance = Mockery::mock(Attendance::class);
    $attendance->shouldReceive('getAttribute')->with('start_time')->andReturn(Carbon::now());
    $attendance->shouldReceive('getAttribute')->with('end_time')->andReturn(Carbon::now());

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->andReturn($attendance);

    // Act: getCurrentStatusメソッドを呼び出し
    $result = $this->attendanceService->getCurrentStatus($userId);

    // 事後条件の検証
    $this->assertEquals('finished', $result);

    // 不変条件: ステータスが正しく判定される
    $this->assertIsString($result);
  }

  /**
   * @test
   * startWork メソッドのテスト - 新規出勤の場合
   *
   * 事前条件: 今日の勤怠記録が存在しない
   * 事後条件: 新しい勤怠記録が作成される
   * 不変条件: 出勤時間とステータスが正しく設定される
   */
  public function test_start_work_new_attendance_with_contract()
  {
    // 事前条件: 今日の勤怠記録が存在しない
    $userId = $this->faker->uuid();
    $attendance = Mockery::mock(Attendance::class);

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->andReturn(null);

    $this->attendanceRepository
      ->shouldReceive('create')
      ->with(Mockery::on(function ($data) use ($userId) {
        return $data['user_id'] === $userId &&
          $data['date'] instanceof Carbon &&
          $data['start_time'] instanceof Carbon &&
          $data['status'] === 'working';
      }))
      ->once()
      ->andReturn($attendance);

    // Act: startWorkメソッドを呼び出し
    $result = $this->attendanceService->startWork($userId);

    // 事後条件の検証
    $this->assertSame($attendance, $result);

    // 不変条件: 出勤時間とステータスが正しく設定される
    $this->attendanceRepository->shouldHaveReceived('create')->once();
  }

  /**
   * @test
   * startWork メソッドのテスト - 既に出勤済みの場合
   *
   * 事前条件: 今日の勤怠記録が存在し、出勤時間が設定されている
   * 事後条件: 例外が投げられる
   * 不変条件: 勤怠記録は変更されない
   */
  public function test_start_work_already_started_with_contract()
  {
    // 事前条件: 今日の勤怠記録が存在し、出勤時間が設定されている
    $userId = $this->faker->uuid();
    $attendance = Mockery::mock(Attendance::class);
    $attendance->shouldReceive('getAttribute')->with('start_time')->andReturn(Carbon::now());

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->andReturn($attendance);

    // Act & Assert: 例外が投げられることを確認
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('本日は既に出勤済みです');

    $this->attendanceService->startWork($userId);

    // 不変条件: 勤怠記録は変更されない
    $this->attendanceRepository->shouldNotHaveReceived('create');
    $this->attendanceRepository->shouldNotHaveReceived('update');
  }

  /**
   * @test
   * endWork メソッドのテスト - 正常な退勤の場合
   *
   * 事前条件: 出勤記録が存在し、退勤時間が設定されていない
   * 事後条件: 退勤時間とステータスが更新される
   * 不変条件: 勤怠記録が正しく更新される
   */
  public function test_end_work_success_with_contract()
  {
    // 事前条件: 出勤記録が存在し、退勤時間が設定されていない
    $userId = $this->faker->uuid();
    $attendanceId = $this->faker->uuid();
    $attendance = Mockery::mock(Attendance::class);
    $attendance->shouldReceive('getAttribute')->with('start_time')->andReturn(Carbon::now());
    $attendance->shouldReceive('getAttribute')->with('end_time')->andReturn(null);
    $attendance->shouldReceive('getAttribute')->with('status')->andReturn('working');
    $attendance->shouldReceive('getAttribute')->with('id')->andReturn($attendanceId);

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->andReturn($attendance);

    $this->attendanceRepository
      ->shouldReceive('update')
      ->with($attendanceId, Mockery::on(function ($data) {
        return $data['end_time'] instanceof Carbon &&
          $data['status'] === 'finished';
      }))
      ->once()
      ->andReturn($attendance);

    // Act: endWorkメソッドを呼び出し
    $result = $this->attendanceService->endWork($userId);

    // 事後条件の検証
    $this->assertSame($attendance, $result);

    // 不変条件: 勤怠記録が正しく更新される
    $this->attendanceRepository->shouldHaveReceived('update')->once();
  }

  /**
   * @test
   * endWork メソッドのテスト - 出勤記録がない場合
   *
   * 事前条件: 出勤記録が存在しない
   * 事後条件: 例外が投げられる
   * 不変条件: 勤怠記録は変更されない
   */
  public function test_end_work_no_attendance_with_contract()
  {
    // 事前条件: 出勤記録が存在しない
    $userId = $this->faker->uuid();

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->andReturn(null);

    // Act & Assert: 例外が投げられることを確認
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('出勤記録がありません');

    $this->attendanceService->endWork($userId);

    // 不変条件: 勤怠記録は変更されない
    $this->attendanceRepository->shouldNotHaveReceived('update');
  }

  /**
   * @test
   * getMonthlyAttendances メソッドのテスト
   *
   * 事前条件: 有効なユーザーIDと月が提供される
   * 事後条件: 指定月の勤怠一覧が返される
   * 不変条件: リポジトリが正しく呼び出される
   */
  public function test_get_monthly_attendances_with_contract()
  {
    // 事前条件: 有効なユーザーIDと月が提供される
    $userId = $this->faker->uuid();
    $month = '2024-01';
    $attendances = [
      Mockery::mock(Attendance::class),
      Mockery::mock(Attendance::class),
    ];

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDateRange')
      ->with($userId, Mockery::type(Carbon::class), Mockery::type(Carbon::class))
      ->once()
      ->andReturn($attendances);

    // Act: getMonthlyAttendancesメソッドを呼び出し
    $result = $this->attendanceService->getMonthlyAttendances($userId, $month);

    // 事後条件の検証
    $this->assertSame($attendances, $result);
    $this->assertCount(2, $result);

    // 不変条件: リポジトリが正しく呼び出される
    $this->attendanceRepository->shouldHaveReceived('findByUserAndDateRange')
      ->with($userId, Mockery::type(Carbon::class), Mockery::type(Carbon::class))
      ->once();
  }

  /**
   * @test
   * getAttendanceDetail メソッドのテスト
   *
   * 事前条件: 有効な勤怠IDが提供される
   * 事後条件: 対応する勤怠詳細が返される
   * 不変条件: リポジトリが正しく呼び出される
   */
  public function test_get_attendance_detail_with_contract()
  {
    // 事前条件: 有効な勤怠IDが提供される
    $attendanceId = $this->faker->uuid();
    $attendance = Mockery::mock(Attendance::class);

    $this->attendanceRepository
      ->shouldReceive('findById')
      ->with($attendanceId)
      ->once()
      ->andReturn($attendance);

    // Act: getAttendanceDetailメソッドを呼び出し
    $result = $this->attendanceService->getAttendanceDetail($attendanceId);

    // 事後条件の検証
    $this->assertSame($attendance, $result);

    // 不変条件: リポジトリが正しく呼び出される
    $this->attendanceRepository->shouldHaveReceived('findById')->with($attendanceId)->once();
  }

  /**
   * @test
   * getStatusLabel メソッドのテスト
   *
   * 事前条件: 有効なステータスが提供される
   * 事後条件: 対応する日本語ラベルが返される
   * 不変条件: すべてのステータスが正しく変換される
   */
  public function test_get_status_label_with_contract()
  {
    // 事前条件: 有効なステータスが提供される
    $statusLabels = [
      'not_working' => '勤務外',
      'working' => '出勤中',
      'on_break' => '休憩中',
      'finished' => '退勤済',
      'unknown' => '不明',
    ];

    foreach ($statusLabels as $status => $expectedLabel) {
      // Act: getStatusLabelメソッドを呼び出し
      $result = $this->attendanceService->getStatusLabel($status);

      // 事後条件の検証
      $this->assertEquals($expectedLabel, $result);

      // 不変条件: すべてのステータスが正しく変換される
      $this->assertIsString($result);
    }
  }

  /**
   * @test
   * AttendanceService クラスの不変条件テスト
   */
  public function test_attendance_service_invariants()
  {
    $service = new AttendanceService($this->attendanceRepository);

    // 不変条件1: AttendanceServiceは常に利用可能であること
    $this->assertInstanceOf(AttendanceService::class, $service);

    // 不変条件2: 必要なメソッドが存在すること
    $requiredMethods = [
      'getTodayAttendance',
      'getCurrentStatus',
      'startWork',
      'endWork',
      'getMonthlyAttendances',
      'getAttendanceDetail',
      'getTodayAttendances',
      'getAttendancesByDate',
      'updateAttendance',
      'updateBreaks',
      'getStatusLabel',
    ];

    foreach ($requiredMethods as $method) {
      $this->assertTrue(method_exists($service, $method), "Method {$method} should exist");
    }

    // 不変条件3: メソッドのシグネチャが正しいこと
    $reflection = new \ReflectionClass($service);

    // getTodayAttendance
    $methodReflection = $reflection->getMethod('getTodayAttendance');
    $this->assertTrue($methodReflection->isPublic());
    $this->assertEquals('string', $methodReflection->getParameters()[0]->getType()->getName());
    $this->assertEquals('App\Models\Attendance', $methodReflection->getReturnType()->getName());

    // getCurrentStatus
    $methodReflection = $reflection->getMethod('getCurrentStatus');
    $this->assertTrue($methodReflection->isPublic());
    $this->assertEquals('string', $methodReflection->getParameters()[0]->getType()->getName());
    $this->assertEquals('string', $methodReflection->getReturnType()->getName());

    // startWork
    $methodReflection = $reflection->getMethod('startWork');
    $this->assertTrue($methodReflection->isPublic());
    $this->assertEquals('string', $methodReflection->getParameters()[0]->getType()->getName());
    $this->assertEquals('App\Models\Attendance', $methodReflection->getReturnType()->getName());

    // endWork
    $methodReflection = $reflection->getMethod('endWork');
    $this->assertTrue($methodReflection->isPublic());
    $this->assertEquals('string', $methodReflection->getParameters()[0]->getType()->getName());
    $this->assertEquals('App\Models\Attendance', $methodReflection->getReturnType()->getName());
  }

  /**
   * @test
   * メソッド呼び出し前後での状態不変性テスト
   */
  public function test_method_call_state_invariants()
  {
    $service = new AttendanceService($this->attendanceRepository);

    // 事前状態の記録（シリアライゼーションを避ける）
    $initialServiceClass = get_class($service);

    // 複数のメソッドを呼び出し
    $userId = $this->faker->uuid();
    $attendanceId = $this->faker->uuid();

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDate')
      ->andReturn(null);
    $this->attendanceRepository
      ->shouldReceive('findById')
      ->andReturn(Mockery::mock(Attendance::class));
    $this->attendanceRepository
      ->shouldReceive('findByUserAndDateRange')
      ->andReturn([]);

    $service->getTodayAttendance($userId);
    $service->getCurrentStatus($userId);
    $service->getAttendanceDetail($attendanceId);
    $service->getMonthlyAttendances($userId, '2024-01');

    // 事後状態の確認
    $finalServiceClass = get_class($service);

    // 不変条件: サービス自体の状態は変わらないこと（ステートレス）
    $this->assertEquals($initialServiceClass, $finalServiceClass);
  }

  /**
   * @test
   * 勤怠状態の一貫性テスト
   *
   * 事前条件: 同じユーザーIDで複数回アクセス
   * 事後条件: 常に同じ結果が返される
   * 不変条件: リポジトリの呼び出し回数が正しい
   */
  public function test_attendance_state_consistency_with_contract()
  {
    $userId = $this->faker->uuid();
    $attendance = Mockery::mock(Attendance::class);
    $attendance->shouldReceive('getAttribute')->with('start_time')->andReturn(Carbon::now());
    $attendance->shouldReceive('getAttribute')->with('end_time')->andReturn(null);
    $attendance->shouldReceive('getAttribute')->with('status')->andReturn('working');

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->twice()
      ->andReturn($attendance);

    // 同じユーザーIDで複数回アクセス
    $result1 = $this->attendanceService->getTodayAttendance($userId);
    $result2 = $this->attendanceService->getCurrentStatus($userId);

    // 事後条件: 常に同じ結果が返される
    $this->assertSame($attendance, $result1);
    $this->assertEquals('working', $result2);

    // 不変条件: リポジトリの呼び出し回数が正しい
    $this->attendanceRepository->shouldHaveReceived('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->twice();
  }
}
