<?php

namespace EgalFramework\Model\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class XssShieldingCast implements CastsAttributes
{

    /**
     * Cast the given value.
     *
     * @param mixed $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return string
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (is_string($value)) {
            return strip_tags($value);
        }
        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param mixed $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (is_string($value)) {
            return strip_tags($value);
        }
        return $value;
    }

}