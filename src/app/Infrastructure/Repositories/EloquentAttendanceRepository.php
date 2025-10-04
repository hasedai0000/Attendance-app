<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Attendance\Repositories\AttendanceRepositoryInterface;
use App\Models\Attendance;
use Carbon\Carbon;

class EloquentAttendanceRepository implements AttendanceRepositoryInterface
{
  public function create(array $data): Attendance
  {
    return Attendance::create($data);
  }

  public function update(string $id, array $data): Attendance
  {
    $attendance = Attendance::findOrFail($id);
    $attendance->update($data);
    return $attendance->fresh();
  }

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

  public function findAll(string $searchTerm): array
  {
    $query = Attendance::with(['user']);

    if ($searchTerm) {
      $query->whereHas('user', function ($q) use ($searchTerm) {
        $q->where('name', 'like', '%' . $searchTerm . '%');
      });
    }

    return $query->get()->toArray();
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
