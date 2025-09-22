<?php

namespace App\Domain\Attendance\ValueObjects;

class Status
{
    // 勤怠状態の定数
    public const NOT_WORKING = 'not_working';
    public const WORKING = 'working';
    public const ON_BREAK = 'on_break';
    public const FINISHED = 'finished';

    // 勤怠状態の表示名
    public const LABELS = [
        self::NOT_WORKING => '未出勤',
        self::WORKING => '出勤',
        self::ON_BREAK => '休憩',
        self::FINISHED => '退勤',
    ];

    private string $value;

    public function __construct(string $value)
    {
        $this->validateStatus($value);
        $this->value = $value;
    }

    private function validateStatus(string $value): void
    {
        if (! in_array($value, [
            self::NOT_WORKING,
            self::WORKING,
            self::ON_BREAK,
            self::FINISHED,
        ], true)) {
            throw new \DomainException('無効な勤怠の状態です');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return self::LABELS[$this->value] ?? '';
    }

    public function isEmpty(): bool
    {
        return empty($this->value);
    }

    /**
     * 値オブジェクトの比較（同値性）
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * 全ての勤怠状態のオプションを取得
     */
    public static function getOptions(): array
    {
        return self::LABELS;
    }
}
