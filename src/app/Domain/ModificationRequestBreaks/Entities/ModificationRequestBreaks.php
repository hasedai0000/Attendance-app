<?php

namespace App\Domain\ModificationRequestBreaks\Entities;

class ModificationRequestBreaks
{
    private string $id;
    private string $modificationRequestId;
    private string $requestedStartTime;
    private string $requestedEndTime;

    public function __construct(
        string $id,
        string $modificationRequestId,
        string $requestedStartTime,
        string $requestedEndTime
    ) {
        $this->id = $id;
        $this->modificationRequestId = $modificationRequestId;
        $this->requestedStartTime = $requestedStartTime;
        $this->requestedEndTime = $requestedEndTime;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getModificationRequestId(): string
    {
        return $this->modificationRequestId;
    }

    public function getRequestedStartTime(): string
    {
        return $this->requestedStartTime;
    }

    public function getRequestedEndTime(): string
    {
        return $this->requestedEndTime;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function setModificationRequestId(string $modificationRequestId): void
    {
        $this->modificationRequestId = $modificationRequestId;
    }

    public function setRequestedStartTime(string $requestedStartTime): void
    {
        $this->requestedStartTime = $requestedStartTime;
    }

    public function setRequestedEndTime(string $requestedEndTime): void
    {
        $this->requestedEndTime = $requestedEndTime;
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
            'modificationRequestId' => $this->modificationRequestId,
            'requestedStartTime' => $this->requestedStartTime,
            'requestedEndTime' => $this->requestedEndTime,
        ];
    }
}
