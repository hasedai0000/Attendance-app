<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Application\Services\BreaksService;
use App\Domain\Breaks\Repositories\BreaksRepositoryInterface;
use App\Domain\Attendance\Repositories\AttendanceRepositoryInterface;
use App\Models\Breaks;
use App\Models\Attendance;
use Carbon\Carbon;
use Mockery;

/**
 * BreaksServiceのテストクラス
 * 契約による設計（Design by Contract）に基づいてテストを実装
 * 各メソッドの事前条件、事後条件、不変条件を検証
 */
class BreaksServiceTest extends TestCase
{
  use WithFaker;

  private BreaksRepositoryInterface $breaksRepository;
  private AttendanceRepositoryInterface $attendanceRepository;
  private BreaksService $breaksService;

  protected function setUp(): void
  {
    parent::setUp();

    $this->breaksRepository = Mockery::mock(BreaksRepositoryInterface::class);
    $this->attendanceRepository = Mockery::mock(AttendanceRepositoryInterface::class);
    $this->breaksService = new BreaksService($this->breaksRepository, $this->attendanceRepository);
  }

  protected function tearDown(): void
  {
    Mockery::close();
    parent::tearDown();
  }

  /**
   * @test
   * startBreak メソッドのテスト - 正常な休憩開始の場合
   *
   * 事前条件: 出勤中の勤怠記録が存在する
   * 事後条件: 休憩記録が作成され、勤怠ステータスが更新される
   * 不変条件: 休憩記録と勤怠記録が正しく更新される
   */
  public function test_start_break_success_with_contract()
  {
    // 事前条件: 出勤中の勤怠記録が存在する
    $userId = $this->faker->uuid();
    $attendanceId = $this->faker->uuid();
    $attendance = Mockery::mock(Attendance::class);
    $attendance->shouldReceive('getAttribute')->with('status')->andReturn('working');
    $attendance->shouldReceive('getAttribute')->with('id')->andReturn($attendanceId);

    $break = Mockery::mock(Breaks::class);

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->andReturn($attendance);

    $this->breaksRepository
      ->shouldReceive('create')
      ->with(Mockery::on(function ($data) use ($attendanceId) {
        return $data['attendance_id'] === $attendanceId &&
          $data['start_time'] instanceof Carbon;
      }))
      ->once()
      ->andReturn($break);

    $this->attendanceRepository
      ->shouldReceive('update')
      ->with($attendanceId, ['status' => 'on_break'])
      ->once()
      ->andReturn($attendance);

    // Act: startBreakメソッドを呼び出し
    $result = $this->breaksService->startBreak($userId);

    // 事後条件の検証
    $this->assertSame($break, $result);

    // 不変条件: 休憩記録と勤怠記録が正しく更新される
    $this->breaksRepository->shouldHaveReceived('create')->once();
    $this->attendanceRepository->shouldHaveReceived('update')->once();
  }

  /**
   * @test
   * startBreak メソッドのテスト - 出勤中でない場合
   *
   * 事前条件: 勤怠記録が存在しないか、出勤中でない
   * 事後条件: 例外が投げられる
   * 不変条件: 休憩記録は作成されない
   */
  public function test_start_break_not_working_with_contract()
  {
    // 事前条件: 勤怠記録が存在しないか、出勤中でない
    $userId = $this->faker->uuid();

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->andReturn(null);

    // Act & Assert: 例外が投げられることを確認
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('出勤中でないため休憩を開始できません');

    $this->breaksService->startBreak($userId);

    // 不変条件: 休憩記録は作成されない
    $this->breaksRepository->shouldNotHaveReceived('create');
    $this->attendanceRepository->shouldNotHaveReceived('update');
  }

  /**
   * @test
   * startBreak メソッドのテスト - 既に休憩中の場合
   *
   * 事前条件: 既に休憩中の勤怠記録が存在する
   * 事後条件: 例外が投げられる
   * 不変条件: 休憩記録は作成されない
   */
  public function test_start_break_already_on_break_with_contract()
  {
    // 事前条件: 既に休憩中の勤怠記録が存在する
    $userId = $this->faker->uuid();
    $attendance = Mockery::mock(Attendance::class);
    $attendance->shouldReceive('getAttribute')->with('status')->andReturn('on_break');

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->andReturn($attendance);

    // Act & Assert: 例外が投げられることを確認
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('出勤中でないため休憩を開始できません');

    $this->breaksService->startBreak($userId);

    // 不変条件: 休憩記録は作成されない
    $this->breaksRepository->shouldNotHaveReceived('create');
    $this->attendanceRepository->shouldNotHaveReceived('update');
  }

  /**
   * @test
   * endBreak メソッドのテスト - 正常な休憩終了の場合
   *
   * 事前条件: 休憩中の勤怠記録と未終了の休憩記録が存在する
   * 事後条件: 休憩終了時刻が設定され、勤怠ステータスが更新される
   * 不変条件: 休憩記録と勤怠記録が正しく更新される
   */
  public function test_end_break_success_with_contract()
  {
    // 事前条件: 休憩中の勤怠記録と未終了の休憩記録が存在する
    $userId = $this->faker->uuid();
    $attendanceId = $this->faker->uuid();
    $breakId = $this->faker->uuid();

    $attendance = Mockery::mock(Attendance::class);
    $attendance->shouldReceive('getAttribute')->with('status')->andReturn('on_break');
    $attendance->shouldReceive('getAttribute')->with('id')->andReturn($attendanceId);

    $activeBreak = Mockery::mock(Breaks::class);
    $activeBreak->shouldReceive('getAttribute')->with('id')->andReturn($breakId);

    $updatedBreak = Mockery::mock(Breaks::class);

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->andReturn($attendance);

    $this->breaksRepository
      ->shouldReceive('findActiveBreakByAttendance')
      ->with($attendanceId)
      ->andReturn($activeBreak);

    $this->breaksRepository
      ->shouldReceive('update')
      ->with($breakId, Mockery::on(function ($data) {
        return $data['end_time'] instanceof Carbon;
      }))
      ->once()
      ->andReturn($updatedBreak);

    $this->attendanceRepository
      ->shouldReceive('update')
      ->with($attendanceId, ['status' => 'working'])
      ->once()
      ->andReturn($attendance);

    // Act: endBreakメソッドを呼び出し
    $result = $this->breaksService->endBreak($userId);

    // 事後条件の検証
    $this->assertSame($updatedBreak, $result);

    // 不変条件: 休憩記録と勤怠記録が正しく更新される
    $this->breaksRepository->shouldHaveReceived('update')->once();
    $this->attendanceRepository->shouldHaveReceived('update')->once();
  }

  /**
   * @test
   * endBreak メソッドのテスト - 休憩中でない場合
   *
   * 事前条件: 勤怠記録が存在しないか、休憩中でない
   * 事後条件: 例外が投げられる
   * 不変条件: 休憩記録は更新されない
   */
  public function test_end_break_not_on_break_with_contract()
  {
    // 事前条件: 勤怠記録が存在しないか、休憩中でない
    $userId = $this->faker->uuid();

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->andReturn(null);

    // Act & Assert: 例外が投げられることを確認
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('休憩中でないため休憩を終了できません');

    $this->breaksService->endBreak($userId);

    // 不変条件: 休憩記録は更新されない
    $this->breaksRepository->shouldNotHaveReceived('update');
    $this->attendanceRepository->shouldNotHaveReceived('update');
  }

  /**
   * @test
   * endBreak メソッドのテスト - 未終了の休憩が見つからない場合
   *
   * 事前条件: 休憩中の勤怠記録は存在するが、未終了の休憩記録が存在しない
   * 事後条件: 例外が投げられる
   * 不変条件: 休憩記録は更新されない
   */
  public function test_end_break_no_active_break_with_contract()
  {
    // 事前条件: 休憩中の勤怠記録は存在するが、未終了の休憩記録が存在しない
    $userId = $this->faker->uuid();
    $attendanceId = $this->faker->uuid();

    $attendance = Mockery::mock(Attendance::class);
    $attendance->shouldReceive('getAttribute')->with('status')->andReturn('on_break');
    $attendance->shouldReceive('getAttribute')->with('id')->andReturn($attendanceId);

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDate')
      ->with($userId, Mockery::type(Carbon::class))
      ->andReturn($attendance);

    $this->breaksRepository
      ->shouldReceive('findActiveBreakByAttendance')
      ->with($attendanceId)
      ->andReturn(null);

    // Act & Assert: 例外が投げられることを確認
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('開始中の休憩が見つかりません');

    $this->breaksService->endBreak($userId);

    // 不変条件: 休憩記録は更新されない
    $this->breaksRepository->shouldNotHaveReceived('update');
    $this->attendanceRepository->shouldNotHaveReceived('update');
  }

  /**
   * @test
   * getBreaksByAttendance メソッドのテスト
   *
   * 事前条件: 有効な勤怠IDが提供される
   * 事後条件: 対応する休憩一覧が返される
   * 不変条件: リポジトリが正しく呼び出される
   */
  public function test_get_breaks_by_attendance_with_contract()
  {
    // 事前条件: 有効な勤怠IDが提供される
    $attendanceId = $this->faker->uuid();
    $breaks = [
      Mockery::mock(Breaks::class),
      Mockery::mock(Breaks::class),
    ];

    $this->breaksRepository
      ->shouldReceive('findByAttendanceId')
      ->with($attendanceId)
      ->once()
      ->andReturn($breaks);

    // Act: getBreaksByAttendanceメソッドを呼び出し
    $result = $this->breaksService->getBreaksByAttendance($attendanceId);

    // 事後条件の検証
    $this->assertSame($breaks, $result);
    $this->assertCount(2, $result);

    // 不変条件: リポジトリが正しく呼び出される
    $this->breaksRepository->shouldHaveReceived('findByAttendanceId')->with($attendanceId)->once();
  }

  /**
   * @test
   * calculateTotalBreakMinutes メソッドのテスト - 正常な休憩時間の計算
   *
   * 事前条件: 開始時刻と終了時刻が設定された休憩記録の配列が提供される
   * 事後条件: 休憩時間の合計（分単位）が返される
   * 不変条件: 計算結果が正しい
   */
  public function test_calculate_total_break_minutes_with_contract()
  {
    // 事前条件: 開始時刻と終了時刻が設定された休憩記録の配列が提供される
    $startTime1 = Carbon::now()->subHours(1);
    $endTime1 = Carbon::now();
    $startTime2 = Carbon::now()->subMinutes(30);
    $endTime2 = Carbon::now();

    $break1 = Mockery::mock(Breaks::class);
    $break1->shouldReceive('getAttribute')->with('start_time')->andReturn($startTime1);
    $break1->shouldReceive('getAttribute')->with('end_time')->andReturn($endTime1);

    $break2 = Mockery::mock(Breaks::class);
    $break2->shouldReceive('getAttribute')->with('start_time')->andReturn($startTime2);
    $break2->shouldReceive('getAttribute')->with('end_time')->andReturn($endTime2);

    $breaks = [$break1, $break2];

    // Act: calculateTotalBreakMinutesメソッドを呼び出し
    $result = $this->breaksService->calculateTotalBreakMinutes($breaks);

    // 事後条件の検証
    $expectedMinutes = 60 + 30; // 1時間 + 30分
    $this->assertEquals($expectedMinutes, $result);

    // 不変条件: 計算結果が正しい
    $this->assertIsInt($result);
    $this->assertGreaterThan(0, $result);
  }

  /**
   * @test
   * calculateTotalBreakMinutes メソッドのテスト - 終了時刻が設定されていない場合
   *
   * 事前条件: 終了時刻が設定されていない休憩記録が含まれる
   * 事後条件: 終了時刻が設定された休憩のみが計算される
   * 不変条件: 計算結果が正しい
   */
  public function test_calculate_total_break_minutes_incomplete_breaks_with_contract()
  {
    // 事前条件: 終了時刻が設定されていない休憩記録が含まれる
    $startTime1 = Carbon::now()->subHours(1);
    $endTime1 = Carbon::now();
    $startTime2 = Carbon::now()->subMinutes(30);

    $break1 = Mockery::mock(Breaks::class);
    $break1->shouldReceive('getAttribute')->with('start_time')->andReturn($startTime1);
    $break1->shouldReceive('getAttribute')->with('end_time')->andReturn($endTime1);

    $break2 = Mockery::mock(Breaks::class);
    $break2->shouldReceive('getAttribute')->with('start_time')->andReturn($startTime2);
    $break2->shouldReceive('getAttribute')->with('end_time')->andReturn(null);

    $breaks = [$break1, $break2];

    // Act: calculateTotalBreakMinutesメソッドを呼び出し
    $result = $this->breaksService->calculateTotalBreakMinutes($breaks);

    // 事後条件の検証
    $expectedMinutes = 60; // 1時間のみ（終了時刻が設定されていない休憩は除外）
    $this->assertEquals($expectedMinutes, $result);

    // 不変条件: 計算結果が正しい
    $this->assertIsInt($result);
    $this->assertGreaterThan(0, $result);
  }

  /**
   * @test
   * calculateTotalBreakMinutes メソッドのテスト - 空の配列の場合
   *
   * 事前条件: 空の休憩記録配列が提供される
   * 事後条件: 0が返される
   * 不変条件: 計算結果が正しい
   */
  public function test_calculate_total_break_minutes_empty_array_with_contract()
  {
    // 事前条件: 空の休憩記録配列が提供される
    $breaks = [];

    // Act: calculateTotalBreakMinutesメソッドを呼び出し
    $result = $this->breaksService->calculateTotalBreakMinutes($breaks);

    // 事後条件の検証
    $this->assertEquals(0, $result);

    // 不変条件: 計算結果が正しい
    $this->assertIsInt($result);
    $this->assertEquals(0, $result);
  }

  /**
   * @test
   * BreaksService クラスの不変条件テスト
   */
  public function test_breaks_service_invariants()
  {
    $service = new BreaksService($this->breaksRepository, $this->attendanceRepository);

    // 不変条件1: BreaksServiceは常に利用可能であること
    $this->assertInstanceOf(BreaksService::class, $service);

    // 不変条件2: 必要なメソッドが存在すること
    $requiredMethods = [
      'startBreak',
      'endBreak',
      'getBreaksByAttendance',
      'calculateTotalBreakMinutes',
    ];

    foreach ($requiredMethods as $method) {
      $this->assertTrue(method_exists($service, $method), "Method {$method} should exist");
    }

    // 不変条件3: メソッドのシグネチャが正しいこと
    $reflection = new \ReflectionClass($service);

    // startBreak
    $methodReflection = $reflection->getMethod('startBreak');
    $this->assertTrue($methodReflection->isPublic());
    $this->assertEquals('string', $methodReflection->getParameters()[0]->getType()->getName());
    $this->assertEquals('App\Models\Breaks', $methodReflection->getReturnType()->getName());

    // endBreak
    $methodReflection = $reflection->getMethod('endBreak');
    $this->assertTrue($methodReflection->isPublic());
    $this->assertEquals('string', $methodReflection->getParameters()[0]->getType()->getName());
    $this->assertEquals('App\Models\Breaks', $methodReflection->getReturnType()->getName());

    // getBreaksByAttendance
    $methodReflection = $reflection->getMethod('getBreaksByAttendance');
    $this->assertTrue($methodReflection->isPublic());
    $this->assertEquals('string', $methodReflection->getParameters()[0]->getType()->getName());
    $this->assertEquals('array', $methodReflection->getReturnType()->getName());

    // calculateTotalBreakMinutes
    $methodReflection = $reflection->getMethod('calculateTotalBreakMinutes');
    $this->assertTrue($methodReflection->isPublic());
    $this->assertEquals('array', $methodReflection->getParameters()[0]->getType()->getName());
    $this->assertEquals('int', $methodReflection->getReturnType()->getName());
  }

  /**
   * @test
   * メソッド呼び出し前後での状態不変性テスト
   */
  public function test_method_call_state_invariants()
  {
    $service = new BreaksService($this->breaksRepository, $this->attendanceRepository);

    // 事前状態の記録（シリアライゼーションを避ける）
    $initialServiceClass = get_class($service);

    // 複数のメソッドを呼び出し
    $userId = $this->faker->uuid();
    $attendanceId = $this->faker->uuid();

    $this->attendanceRepository
      ->shouldReceive('findByUserAndDate')
      ->andReturn(null);
    $this->breaksRepository
      ->shouldReceive('findByAttendanceId')
      ->andReturn([]);

    $service->getBreaksByAttendance($attendanceId);
    $service->calculateTotalBreakMinutes([]);

    // 事後状態の確認
    $finalServiceClass = get_class($service);

    // 不変条件: サービス自体の状態は変わらないこと（ステートレス）
    $this->assertEquals($initialServiceClass, $finalServiceClass);
  }

  /**
   * @test
   * 休憩状態の一貫性テスト
   *
   * 事前条件: 同じ勤怠IDで複数回アクセス
   * 事後条件: 常に同じ結果が返される
   * 不変条件: リポジトリの呼び出し回数が正しい
   */
  public function test_break_state_consistency_with_contract()
  {
    $attendanceId = $this->faker->uuid();
    $breaks = [
      Mockery::mock(Breaks::class),
    ];

    $this->breaksRepository
      ->shouldReceive('findByAttendanceId')
      ->with($attendanceId)
      ->twice()
      ->andReturn($breaks);

    // 同じ勤怠IDで複数回アクセス
    $result1 = $this->breaksService->getBreaksByAttendance($attendanceId);
    $result2 = $this->breaksService->getBreaksByAttendance($attendanceId);

    // 事後条件: 常に同じ結果が返される
    $this->assertSame($breaks, $result1);
    $this->assertSame($breaks, $result2);

    // 不変条件: リポジトリの呼び出し回数が正しい
    $this->breaksRepository->shouldHaveReceived('findByAttendanceId')->with($attendanceId)->twice();
  }
}
