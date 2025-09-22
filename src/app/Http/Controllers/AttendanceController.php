<?php

namespace App\Http\Controllers;

use App\Application\Services\AttendanceService;

class AttendanceController extends Controller
{
    private AttendanceService $attendanceService;

    public function __construct(
        AttendanceService $attendanceService
    ) {
        $this->attendanceService = $attendanceService;
    }
}
