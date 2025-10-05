<?php

namespace App\Domain\Common\ValueObjects;

abstract class BaseValueObject
{
  protected $value;

  public function __construct($value)
  {
    $this->validate($value);
    $this->value = $value;
  }

  /**
   * 値の検証を行う（サブクラスで実装）
   */
  abstract protected function validate($value): void;

  /**
   * 値を取得
   */
  public function value()
  {
    return $this->value;
  }

  /**
   * 値が空かどうかを判定
   */
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

  /**
   * 文字列としても利用可能
   */
  public function __toString(): string
  {
    return (string) $this->value;
  }
}
