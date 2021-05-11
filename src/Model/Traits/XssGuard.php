<?php

namespace EgalFramework\Model\Traits;

use EgalFramework\Model\Casts\XssShieldingCast;

trait XssGuard
{

    /**
     * Поля игнорируемые XSS защитой на ввод и вывод
     *
     * @var array
     */
    protected array $ignoreXssShieldingFields = [];

    /**
     * Выставление  ShieldingCast на все поля,
     * кроме тех, что указаны в ignoreXssShieldingFields
     */
    public function initializeXssGuard()
    {
        $casts = [];
        $fieldNames = array_diff($this->metadata->getFieldNames(true), $this->ignoreXssShieldingFields);
        foreach ($fieldNames as $fieldName) {
            $casts[$fieldName] = XssShieldingCast::class;
        }
        $this->mergeCasts($casts);
    }

}