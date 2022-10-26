<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

use Egal\Model\Exceptions\ValidateException;
use Egal\Model\Model;
use Illuminate\Support\Facades\Validator;

trait UsesValidator
{

    /**
     * @param \Closure|string $callback
     */
    public static function validating($callback): void
    {
        static::registerModelEvent('validating', $callback);
    }

    /**
     * @param \Closure|string $callback
     */
    public static function validated($callback): void
    {
        static::registerModelEvent('validated', $callback);
    }

    /**
     * @throws ValidateException
     */
    protected function validate(): void
    {
        // Получаем validation rules
        // всех атрибутов если объект новый,
        // только измененных атрибутов если объект обновляется.
        //
        // Получение validation rules только измененных атрибутов происходит
        // путем получения пересечения всех validation rules и измененный атрибутов по ключам.
        $validationRules = $this->exists
            ? array_intersect_key($this->getValidationRules(), $this->getDirty())
            : $this->getValidationRules();

        // Применяем полученные validation rules на все атрибуты модели.
        $validator = Validator::make($this->getAttributes(), $validationRules);

        if ($validator->fails()) {
            $exception = new ValidateException();
            $exception->setMessageBag($validator->errors());

            throw $exception;
        }
    }

    /**
     * @throws ValidateException
     */
    protected function validateKey(mixed $keyValue): void
    {
        $primaryKey = $this->getModelMetadata()->getKey()->getName();

        if ($primaryKey === null) {
            $validator = Validator::make(
                [$this->getKeyName() => $keyValue],
                [$this->getKeyName() => [$this->getKeyType()]]
            );
        } else {
            $validationRules = $this->getModelMetadata()->getKey()->getValidationRules();
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

    /**
     * @throws ValidateException
     * @throws \ReflectionException
     */
    protected static function bootUsesValidator(): void
    {
        static::saving(static function (Model $entity): void {
            $entity->fireModelEvent('validating');
            $entity->validate();
            $entity->fireModelEvent('validated');
        });
    }

}
