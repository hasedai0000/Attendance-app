<?php

namespace App\Domain\ModificationRequest\ValueObjects;

class Status
{
    // 申請状態の定数
    public const PENDING = 'pending';
    public const APPROVED = 'approved';

    // 申請状態の表示名
    public const LABELS = [
        self::PENDING => '未承認',
        self::APPROVED => '承認済み',
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
            self::PENDING,
            self::APPROVED,
        ], true)) {
            throw new \DomainException('無効な申請状態です');
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
     * 全ての申請状態のオプションを取得
     */
    public static function getOptions(): array
    {
        return self::LABELS;
    }
}
