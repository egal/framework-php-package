<?php

namespace Egal\Model\Casts;

/**
 * Cast класс защиты атрибута от XSS защиты на вывод
 *
 * @package Egal\Model
 */
class XssOutputGuardCast extends XssGuardCast
{

    /**
     * Возвращает значение атрибута неизменненым
     *
     * Отменяет действие {@see XssGuardCast::set()}
     *
     * @param mixed $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        return $value;
    }

}
