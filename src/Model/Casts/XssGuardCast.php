<?php

namespace Egal\Model\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Cast класс защиты атрибута от XSS защиты на ввод и на вывод
 *
 * @package Egal\Model
 */
class XssGuardCast implements CastsAttributes
{

    /**
     * Убрирает из строчных атрибутов при выводе все HTML теги.
     *
     * @param mixed $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (is_string($value)) {
            return strip_tags($value);
        }
        return $value;
    }

    /**
     * Убрирает из строчных атрибутов при вводе все HTML теги.
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
