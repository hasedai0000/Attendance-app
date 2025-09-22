<?php

namespace App\Domain\ModificationRequest\Entities;

use App\Domain\ModificationRequest\ValueObjects\Status;

class ModificationRequest
{
    private string $id;
    private string $attendanceId;
    private string $userId;
    private string $requestedStartTime;
    private string $requestedEndTime;
    private string $requestedRemarks;
    private Status $status;
    private string $approvedBy;
    private string $approvedAt;

    public function __construct(
        string $id,
        string $attendanceId,
        string $userId,
        string $requestedStartTime,
        string $requestedEndTime,
        string $requestedRemarks,
        Status $status,
        string $approvedBy,
        string $approvedAt
    ) {
        $this->id = $id;
        $this->attendanceId = $attendanceId;
        $this->userId = $userId;
        $this->requestedStartTime = $requestedStartTime;
        $this->requestedEndTime = $requestedEndTime;
        $this->requestedRemarks = $requestedRemarks;
        $this->status = $status;
        $this->approvedBy = $approvedBy;
        $this->approvedAt = $approvedAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAttendanceId(): string
    {
        return $this->attendanceId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getRequestedStartTime(): string
    {
        return $this->requestedStartTime;
    }

    public function getRequestedEndTime(): string
    {
        return $this->requestedEndTime;
    }

    public function getRequestedRemarks(): string
    {
        return $this->requestedRemarks;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getApprovedBy(): string
    {
        return $this->approvedBy;
    }

    public function getApprovedAt(): string
    {
        return $this->approvedAt;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setAttendanceId(string $attendanceId): void
    {
        $this->attendanceId = $attendanceId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function setRequestedStartTime(string $requestedStartTime): void
    {
        $this->requestedStartTime = $requestedStartTime;
    }

    public function setRequestedEndTime(string $requestedEndTime): void
    {
        $this->requestedEndTime = $requestedEndTime;
    }

    public function setRequestedRemarks(string $requestedRemarks): void
    {
        $this->requestedRemarks = $requestedRemarks;
    }

    public function setStatus(Status $status): void
    {
        $this->status = $status;
    }

    public function setApprovedBy(string $approvedBy): void
    {
        $this->approvedBy = $approvedBy;
    }

    public function setApprovedAt(string $approvedAt): void
    {
        $this->approvedAt = $approvedAt;
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
            'userId' => $this->userId,
            'requestedStartTime' => $this->requestedStartTime,
            'requestedEndTime' => $this->requestedEndTime,
            'requestedRemarks' => $this->requestedRemarks,
            'status' => $this->status->value(),
            'approvedBy' => $this->approvedBy,
            'approvedAt' => $this->approvedAt
        ];
    }
}
