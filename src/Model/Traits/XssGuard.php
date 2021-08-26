<?php

namespace Egal\Model\Traits;

use Egal\Model\Model;
use ReflectionException;

/**
 * Trait защиты от XSS атак.
 *
 * Позволяет защитить как все поля, так и выбранные в {@see XssGuardable::$ignoreXssShieldingFields}.
 * А также имеет возможность переопределения Cast класса в {@see XssGuardable::$xssShieldingCastClass}
 *
 * @mixin Model
 */
trait XssGuard
{

    /**
     * Инициализатор, дополнение конструктора модели
     *
     * При инициализации экземпляра класса выставляет на поля {@see XssGuardable::$xssShieldingCastClass},
     * не указанные в {@see XssGuardable::$ignoreXssShieldingFields}
     *
     * @throws ReflectionException
     * @noinspection PhpUnused
     */
    public function initializeXssGuard()
    {
        # TODO: Проверить используется ли в static XssGuardable
        $casts = [];
        $fieldNames = array_diff($this->getModelMetadata()->getDatabaseFields(), $this->ignoreXssShieldingFields);
        foreach ($fieldNames as $fieldName) {
            $casts[$fieldName] = $this->xssShieldingCastClass;
        }
        $this->mergeCasts($casts);
    }

}
