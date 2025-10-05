<?php

namespace App\Domain\Common\ValueObjects\Traits;

trait ValidationTrait
{
 /**
  * 空文字列チェック
  */
 protected function validateNotEmpty($value, string $fieldName): void
 {
  if (empty(trim($value))) {
   throw new \DomainException($fieldName . 'を入力してください');
  }
 }

 /**
  * 最小文字数チェック
  */
 protected function validateMinLength($value, int $minLength, string $fieldName): void
 {
  if (mb_strlen($value) < $minLength) {
   throw new \DomainException($fieldName . 'は' . $minLength . '文字以上で入力してください');
  }
 }

 /**
  * 最大文字数チェック
  */
 protected function validateMaxLength($value, int $maxLength, string $fieldName): void
 {
  if (mb_strlen($value) > $maxLength) {
   throw new \DomainException($fieldName . 'は' . $maxLength . '文字以下で入力してください');
  }
 }

 /**
  * 配列内の値チェック
  */
 protected function validateInArray($value, array $allowedValues, string $fieldName): void
 {
  if (!in_array($value, $allowedValues, true)) {
   throw new \DomainException('無効な' . $fieldName . 'です');
  }
 }

 /**
  * メールアドレス形式チェック
  */
 protected function validateEmailFormat($value, string $fieldName): void
 {
  if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
   throw new \DomainException($fieldName . 'の形式が正しくありません');
  }
 }
}
