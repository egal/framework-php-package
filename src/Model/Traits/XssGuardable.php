<?php

namespace Egal\Model\Traits;

use Egal\Model\Casts\XssGuardCast;

/**
 * @package Egal\Model
 */
trait XssGuardable
{

    /**
     * Поля игнорируемые XSS защитой на ввод и вывод
     *
     * Attention!: XSS защита работает только при использовании XssGuard Trait
     *
     * @var array
     */
    protected array $ignoreXssShieldingFields = [];

    /**
     * Cast класс используемый для защиты от XSS.
     *
     * Можно переопределить для изменения Cast класса.
     *
     * В Egal доступны три типа Cast классов: {@see XssGuardCast}, {@see XssOutputGuardCast}, {@see XssInputGuardCast}.
     *
     * @var string
     */
    protected string $xssShieldingCastClass = XssGuardCast::class;

}
