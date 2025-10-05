<?php

namespace App\Domain\User\ValueObjects;

use App\Domain\Common\ValueObjects\BaseValueObject;
use App\Domain\Common\ValueObjects\Traits\ValidationTrait;

class UserEmail extends BaseValueObject
{
    use ValidationTrait;

    protected function validate($value): void
    {
        $this->validateNotEmpty($value, 'メールアドレス');
        $this->validateMinLength($value, 1, 'メールアドレス');
        $this->validateEmailFormat($value, 'メールアドレス');
    }
}
