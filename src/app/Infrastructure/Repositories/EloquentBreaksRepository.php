<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Breaks\Repositories\BreaksRepositoryInterface;
use App\Models\Breaks;

class EloquentBreaksRepository implements BreaksRepositoryInterface
{
 public function create(array $data): Breaks
 {
  return Breaks::create($data);
 }

 public function update(string $id, array $data): Breaks
 {
  $break = Breaks::findOrFail($id);
  $break->update($data);
  return $break->fresh();
 }

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

 public function findAll(string $searchTerm): array
 {
  $query = Breaks::with(['attendance.user']);

  if ($searchTerm) {
   $query->whereHas('attendance.user', function ($q) use ($searchTerm) {
    $q->where('name', 'like', '%' . $searchTerm . '%');
   });
  }

  return $query->get()->toArray();
 }
}
