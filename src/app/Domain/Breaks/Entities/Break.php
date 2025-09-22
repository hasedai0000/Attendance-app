<?php

namespace App\Domain\Breaks\Entities;

class Breaks
{
    private string $id;
    private string $attendanceId;
    private string $startTime;
    private string $endTime;

    public function __construct(
        string $id,
        string $attendanceId,
        string $startTime,
        string $endTime
    ) {
        $this->id = $id;
        $this->attendanceId = $attendanceId;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAttendanceId(): string
    {
        return $this->attendanceId;
    }

    public function getStartTime(): string
    {
        return $this->startTime;
    }

    public function getEndTime(): string
    {
        return $this->endTime;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setAttendanceId(string $attendanceId): void
    {
        $this->attendanceId = $attendanceId;
    }

    public function setStartTime(string $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function setEndTime(string $endTime): void
    {
        $this->endTime = $endTime;
    }

    /**
     * エンティティを配列に変換する
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'attendanceId' => $this->attendanceId,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
        ];
    }
}
