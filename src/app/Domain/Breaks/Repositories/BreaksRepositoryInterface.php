<?php

namespace App\Domain\Breaks\Repositories;

use App\Models\Breaks;

interface BreaksRepositoryInterface
{
    public function create(array $data): Breaks;
    public function update(string $id, array $data): Breaks;
    public function findById(string $id): ?Breaks;
    public function findByAttendanceId(string $attendanceId): array;
    public function findActiveBreakByAttendance(string $attendanceId): ?Breaks;
    public function findAll(string $searchTerm): array;
}
