<?php

declare(strict_types=1);

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exception\ValidationException;
use Valitron\Validator;

class UpdateCategoryRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        $v = new Validator($data);
        $v->rule('required', 'name');
        $v->rule('lengthMax', 'name', 50);

        if (!$v->validate()) {
            // Errors
            throw new ValidationException(errors: $v->errors());
        }

        return $data;
    }
}