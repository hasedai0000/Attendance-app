<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Breaks\Repositories\BreaksRepositoryInterface;
use App\Infrastructure\Repositories\Traits\SearchableTrait;
use App\Models\Breaks;

class EloquentBreaksRepository extends BaseEloquentRepository implements BreaksRepositoryInterface
{
  use SearchableTrait;

  public function __construct(Breaks $breaks)
  {
    parent::__construct($breaks);
  }

  /**
   * 基底クラスのcreateメソッドをオーバーライド
   */
  public function create(array $data): Breaks
  {
    return Breaks::create($data);
  }

  /**
   * 基底クラスのupdateメソッドをオーバーライド
   */
  public function update(string $id, array $data): Breaks
  {
    $break = Breaks::findOrFail($id);
    $break->update($data);
    return $break->fresh();
  }

  /**
   * 基底クラスのfindByIdメソッドをオーバーライド
   */
  public function findById(string $id): ?Breaks
  {
    return Breaks::find($id);
  }

  public function findByAttendanceId(string $attendanceId): array
  {
    return Breaks::where('attendance_id', $attendanceId)
      ->orderBy('start_time')
      ->get()
      ->toArray();
  }

  public function findActiveBreakByAttendance(string $attendanceId): ?Breaks
  {
    return Breaks::where('attendance_id', $attendanceId)
      ->whereNull('end_time')
      ->orderBy('start_time', 'desc')
      ->first();
  }

  protected function applySearchFilter($query, string $searchTerm)
  {
    return $this->applyRelatedNameSearch($query->with(['attendance.user']), $searchTerm, 'attendance.user');
  }
}
