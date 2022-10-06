<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

use Egal\Model\Enums\ValidationRules;

trait FieldValidationRules
{

    public function required(): self
    {
        $this->validationRules[] = ValidationRules::REQUIRED->value;

        return $this;
    }

    public function sometimes(): self
    {
        $this->validationRules[] = ValidationRules::SOMETIMES->value;

        return $this;
    }

    public function string(): self
    {
        $this->validationRules[] = ValidationRules::STRING->value;

        return $this;
    }

    public function boolean(): self
    {
        $this->validationRules[] = ValidationRules::BOOLEAN->value;

        return $this;
    }

    public function numeric(): self
    {
        $this->validationRules[] = ValidationRules::NUMERIC->value;

        return $this;
    }

    public function accepted(): self
    {
        $this->validationRules[] = ValidationRules::ACCEPTED->value;

        return $this;
    }

    public function activeUrl(): self
    {
        $this->validationRules[] = ValidationRules::ACTIVE_URL->value;

        return $this;
    }

    public function alpha(): self
    {
        $this->validationRules[] = ValidationRules::ALPHA->value;

        return $this;
    }

    public function alphaDash(): self
    {
        $this->validationRules[] = ValidationRules::ALPHA_DASH->value;

        return $this;
    }

    public function alphaNum(): self
    {
        $this->validationRules[] = ValidationRules::ALPHA_NUM->value;

        return $this;
    }

    public function array(): self
    {
        $this->validationRules[] = ValidationRules::ARRAY->value;

        return $this;
    }

    public function bail(): self
    {
        $this->validationRules[] = ValidationRules::BAIL->value;

        return $this;
    }

    public function confirmed(): self
    {
        $this->validationRules[] = ValidationRules::CONFIRMED->value;

        return $this;
    }

    public function date(): self
    {
        $this->validationRules[] = ValidationRules::DATE->value;

        return $this;
    }

    public function declined(): self
    {
        $this->validationRules[] = ValidationRules::DECLINED->value;

        return $this;
    }

    public function exclude(): self
    {
        $this->validationRules[] = ValidationRules::EXCLUDE->value;

        return $this;
    }

    public function file(): self
    {
        $this->validationRules[] = ValidationRules::FILE->value;

        return $this;
    }

    public function filled(): self
    {
        $this->validationRules[] = ValidationRules::FILLED->value;

        return $this;
    }

    public function image(): self
    {
        $this->validationRules[] = ValidationRules::IMAGE->value;

        return $this;
    }

    public function integer(): self
    {
        $this->validationRules[] = ValidationRules::INTEGER->value;

        return $this;
    }

    public function ip(): self
    {
        $this->validationRules[] = ValidationRules::IP->value;

        return $this;
    }

    public function ipv4(): self
    {
        $this->validationRules[] = ValidationRules::IPV4->value;

        return $this;
    }

    public function ipv6(): self
    {
        $this->validationRules[] = ValidationRules::IPV6->value;

        return $this;
    }

    public function mac_address(): self
    {
        $this->validationRules[] = ValidationRules::MAC_ADDRESS->value;

        return $this;
    }

    public function present(): self
    {
        $this->validationRules[] = ValidationRules::PRESENT->value;

        return $this;
    }

    public function prohibited(): self
    {
        $this->validationRules[] = ValidationRules::PROHIBITED->value;

        return $this;
    }

    public function timezone(): self
    {
        $this->validationRules[] = ValidationRules::TIMEZONE->value;

        return $this;
    }

    public function url(): self
    {
        $this->validationRules[] = ValidationRules::URL->value;

        return $this;
    }

    public function uuid(): self
    {
        $this->validationRules[] = ValidationRules::UUID->value;

        return $this;
    }

}
