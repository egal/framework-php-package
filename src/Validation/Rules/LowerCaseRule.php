<?php

namespace Egal\Validation\Rules;

use Egal\Validation\Rules\Rule as EgalRule;
use Illuminate\Support\Str;

class LowerCaseRule extends EgalRule
{

    public function validate($attribute, $value, $parameters = null): bool
    {
        return Str::lower($value) === $value;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return 'Attribute :attribute not in lower case!';
    }

}
