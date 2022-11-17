<?php

declare(strict_types=1);

namespace Egal\Model\Casts;

/**
 * Cast класс защиты атрибута от XSS защиты на ввод
 *
 * @package Egal\Model
 */
class XssInputGuardCast extends XssGuardCast
{

    /**
     * Возвращает значение атрибута неизменненым
     *
     * Отменяет действие {@see XssGuardCast::get()}
     *
     * @param mixed $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        return $value;
    }

}
