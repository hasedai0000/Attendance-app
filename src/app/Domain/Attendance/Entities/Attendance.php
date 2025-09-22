<?php

namespace App\Domain\Attendance\Entities;

use App\Domain\Attendance\ValueObjects\Status;

class Attendance
{
    private string $id;
    private string $userId;
    private string $date;
    private string $startTime;
    private string $endTime;
    private Status $status;
    private string $remarks;

    public function __construct(
        string $id,
        string $userId,
        string $date,
        string $startTime,
        string $endTime,
        Status $status,
        string $remarks
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->date = $date;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->status = $status;
        $this->remarks = $remarks;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getStartTime(): string
    {
        return $this->startTime;
    }

    public function getEndTime(): string
    {
        return $this->endTime;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getRemarks(): string
    {
        return $this->remarks;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function setDate(string $date): void
    {
        $this->date = $date;
    }

    public function setStartTime(string $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function setEndTime(string $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function setStatus(Status $status): void
    {
        $this->status = $status;
    }

    public function setRemarks(string $remarks): void
    {
        $this->remarks = $remarks;
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
            'userId' => $this->userId,
            'date' => $this->date,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'status' => $this->status->value(),
            'remarks' => $this->remarks,
        ];
    }
}
