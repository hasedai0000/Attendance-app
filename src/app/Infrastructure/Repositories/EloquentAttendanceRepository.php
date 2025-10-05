<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Attendance\Repositories\AttendanceRepositoryInterface;
use App\Infrastructure\Repositories\Traits\SearchableTrait;
use App\Models\Attendance;
use Carbon\Carbon;

class EloquentAttendanceRepository extends BaseEloquentRepository implements AttendanceRepositoryInterface
{
  use SearchableTrait;

  public function __construct(Attendance $attendance)
  {
    parent::__construct($attendance);
  }

  /**
   * 基底クラスのcreateメソッドをオーバーライド
   */
  public function create(array $data): Attendance
  {
    return Attendance::create($data);
  }

  /**
   * 基底クラスのupdateメソッドをオーバーライド
   */
  public function update(string $id, array $data): Attendance
  {
    $attendance = Attendance::findOrFail($id);
    $attendance->update($data);
    return $attendance->fresh();
  }

  /**
   * 基底クラスのfindByIdメソッドをオーバーライド
   */
  public function findById(string $id): ?Attendance
  {
    return Attendance::with(['user', 'breaks'])->find($id);
  }

  public function findByUserAndDate(string $userId, Carbon $date): ?Attendance
  {
    return Attendance::where('user_id', $userId)
      ->whereDate('date', $date)
      ->first();
  }

  public function findByUserAndDateRange(string $userId, Carbon $startDate, Carbon $endDate): array
  {
    return Attendance::where('user_id', $userId)
      ->whereBetween('date', [$startDate, $endDate])
      ->with(['breaks'])
      ->orderBy('date')
      ->get()
      ->toArray();
  }

  protected function applySearchFilter($query, string $searchTerm)
  {
    return $this->applyRelatedNameSearch($query->with(['user']), $searchTerm, 'user');
  }

  public function findByDate(Carbon $date): array
  {
    return Attendance::whereDate('date', $date)
      ->with(['user', 'breaks'])
      ->orderBy('user_id')
      ->get()
      ->toArray();
  }
}
