<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

/**
 * Trait HashGuardable
 * @package Egal\Model
 */
trait HashGuardable
{

    /**
     * @var string[]
     */
    protected array $hashShieldingFields = ['*'];

    /**
     * @var string[]
     */
    protected array $ignoreHashShieldingFields = [];

    /**
     * Получение названия field для хранения hash данных модели
     *
     * Данную функцию можно переопределить,
     * для изменения названия field для хранения hash данных модели
     *
     * @return string
     */
    public function getHashFieldName(): string
    {
        return 'hash';
    }

}
