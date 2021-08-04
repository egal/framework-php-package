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
            // Получаем validation rules
            // всех атрибутов если объект новый,
            // только измененных атрибутов если объект обновляется.
            //
            // Получение validation rules только измененных атрибутов происходит
            // путем получения пересечения всех validation rules и измененный атрибутов по ключам.
            $validationRules = $entity->exists
                ? array_intersect_key($entity->getValidationRules(), $entity->getDirty())
                : $entity->getValidationRules();

            // Применяем полученные validation rules на все атрибуты модели.
            $validator = Validator::make($entity->getAttributes(), $validationRules);

            if ($validator->fails()) {
                $exception = new ValidateException();
                $exception->setMessageBag($validator->errors());

                throw $exception;
            }
        });
    }

    /**
     * @param mixed $keyValue
     * @throws \Egal\Model\Exceptions\ValidateException
     */
    private function validateKey($keyValue): void
    {
        $primaryKey = $this->getModelMetadata()->getPrimaryKey();

        if ($primaryKey === null) {
            $validator = Validator::make(
                [$this->getKeyName() => $keyValue],
                [$this->getKeyName() => [$this->getKeyType()]]
            );
        } else {
            $validationRules = $this->getModelMetadata()->getValidationRules($primaryKey);
            $validator = Validator::make(
                [$primaryKey => $keyValue],
                [$primaryKey => $validationRules === [] ? [$this->getKeyType()] : $validationRules]
            );
        }

        if ($validator->fails()) {
            $exception = new ValidateException();
            $exception->setMessageBag($validator->errors());

            throw $exception;
        }
    }

}
