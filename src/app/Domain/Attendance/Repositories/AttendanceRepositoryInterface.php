<?php

namespace App\Domain\Attendance\Repositories;

interface AttendanceRepositoryInterface
{
    public function findAll(string $searchTerm): array;
}
