<?php

namespace App\Domain\User\ValueObjects;

use App\Domain\Common\ValueObjects\BaseValueObject;
use App\Domain\Common\ValueObjects\Traits\ValidationTrait;

class UserPassword extends BaseValueObject
{
    use ValidationTrait;

    protected function validate($value): void
    {
        $this->validateNotEmpty($value, 'パスワード');
        $this->validateMinLength($value, 8, 'パスワード');
    }
}
