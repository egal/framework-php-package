<?php

declare(strict_types=1);

namespace Egal\Validation\Rules;

use Illuminate\Support\Str;

class UpperCaseRule extends Rule
{

    public function validate($attribute, $value, $parameters = null): bool
    {
        return Str::upper($value) === $value;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return 'Attribute :attribute not in upper case!';
    }

}
