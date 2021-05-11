<?php

namespace EgalFramework\Model\Traits;

use EgalFramework\Model\Exceptions\ValidationException;
use Illuminate\Support\Facades\Validator;

trait UsesValidator
{

    /**
     * @throws ValidationException
     */
    protected static function bootUsesValidator()
    {
        static::saving(function ($entity) {
            // Получаем все измененные атрибуты
            $attributes = $entity->getDirty();

            // Получаем validation rules
            // всех атрибутов если объект новый,
            // только измененных атрибутов если объект обновляется.
            //
            // Получение validation rules только измененных атрибутов происходит
            // путем получения пересечения всех validation rules и измененный атрибутов по ключам
            $validationRules = $entity->exists
                ? array_intersect_key($entity->metadata->getValidationRules(false), $attributes)
                : $entity->metadata->getValidationRules(false);

            // Применяем полученные validation rules на все атрибуты модели
            $validator = Validator::make($entity->getAttributes(), $validationRules);
            if ($validator->fails()) {
                $exception = new ValidationException('Failed to validate!');
                $exception->setErrors($validator->errors()->toArray());
                throw $exception;
            }
        });
    }

}
