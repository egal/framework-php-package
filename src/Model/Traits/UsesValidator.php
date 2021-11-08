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
     * @param \Closure|string $callback
     */
    public static function validatingWithAction($callback): void
    {
        static::registerModelEvent('validating.action', $callback);
    }

    /**
     * @param \Closure|string $callback
     */
    public static function validatedWithAction($callback): void
    {
        static::registerModelEvent('validated.action', $callback);
    }

    /**
     * @throws \Egal\Model\Exceptions\ValidateException
     * @throws \ReflectionException
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
     * @param mixed $keyValue
     * @throws \Egal\Model\Exceptions\ValidateException
     */
    protected function validateKey($keyValue): void
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

    /**
     * @throws \Egal\Model\Exceptions\ValidateException
     * @throws \ReflectionException
     */
    protected static function bootUsesValidator(): void
    {
        static::saving(static function (Model $entity): void {
            $entity->fireModelEvent('validating', true);

            if ($entity->isNeedFireActionEvents()) {
                $entity->fireActionEvent('validating.action', true);
                $entity->validate();
                $entity->fireActionEvent('validated.action', true);
            } else {
                $entity->validate();
            }

            $entity->fireModelEvent('validated', true);
        });
    }

}
