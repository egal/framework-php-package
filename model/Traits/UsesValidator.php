<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

use Egal\Model\Exceptions\ValidateException;
use Egal\Model\Model;
use Illuminate\Support\Facades\Validator;

trait UsesValidator
{

    /**
     * @throws \Egal\Model\Exceptions\ValidateException
     * @throws \ReflectionException
     */
    protected static function bootUsesValidator(): void
    {
        static::saving(static function (Model $entity): void {
            // Получаем все измененные атрибуты
            $attributes = $entity->getDirty();

            // Получаем validation rules
            // всех атрибутов если объект новый,
            // только измененных атрибутов если объект обновляется.
            //
            // Получение validation rules только измененных атрибутов происходит
            // путем получения пересечения всех validation rules и измененный атрибутов по ключам
            $validationRules = $entity->exists
                ? array_intersect_key($entity->getValidationRules(), $attributes)
                : $entity->getValidationRules();

            // Применяем полученные validation rules на все атрибуты модели
            $validator = Validator::make($entity->getAttributes(), $validationRules);

            if ($validator->fails()) {
                $exception = new ValidateException();
                $exception->setMessageBag($validator->errors());

                throw $exception;
            }
        });
    }

    private function validateKey($keyValue): void
    {
        $validator = Validator::make(
            [$this->getKeyName() => $keyValue],
            [$this->getKeyName() => $this->getModelMetadata()->getValidationRules($this->getKeyName())]
        );

        if ($validator->fails()) {
            $exception = new ValidateException();
            $exception->setMessageBag($validator->errors());

            throw $exception;
        }
    }

}
