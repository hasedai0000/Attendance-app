<?php

namespace App\Domain\Attendance\Repositories;

use App\Models\Attendance;
use Carbon\Carbon;

interface AttendanceRepositoryInterface
{
    public function create(array $data): Attendance;
    public function update(string $id, array $data): Attendance;
    public function findById(string $id): ?Attendance;
    public function findByUserAndDate(string $userId, Carbon $date): ?Attendance;
    public function findByUserAndDateRange(string $userId, Carbon $startDate, Carbon $endDate): array;
    public function findAll(string $searchTerm): array;
    public function findByDate(Carbon $date): array;
}
