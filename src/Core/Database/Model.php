<?php

namespace Egal\Core\Database;

use Egal\Core\Database\Metadata\Model as ModelMetadata;
use Egal\Core\Exceptions\ValidateException;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Facades\Validator;

/**
 * #TODO: Реализовать EnumModel.
 */
abstract class Model extends BaseModel
{

    private ModelMetadata $metadata;

    abstract public function initializeMetadata(): ModelMetadata;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        # TODO: Cache remember.
        $this->metadata = $this->initializeMetadata();

        $this->syncMetadata();
    }

    public function getMetadata(): ModelMetadata
    {
        return $this->metadata;
    }

    # TODO: May be need change method name.
    private function syncMetadata(): void
    {
        $metadata = $this->getMetadata();
        $this->mergeFillable($metadata->getFillableFieldsNames());
    }

    public function validate(): void
    {
        // Получаем validation rules
        // всех атрибутов если объект новый,
        // только измененных атрибутов если объект обновляется.
        //
        // Получение validation rules только измененных атрибутов происходит
        // путем получения пересечения всех validation rules и измененный атрибутов по ключам.
        $allValidationRules = $this->getMetadata()->getValidationRules();
        $validationRules = $this->exists
            ? array_intersect_key($allValidationRules, $this->getDirty())
            : $allValidationRules;


        # TODO: Add messages.
        # TODO: What is $customAttributes param in Validator::make.
        // Применяем полученные validation rules на все атрибуты модели.
        $validator = Validator::make($this->getAttributes(), $validationRules);

        if ($validator->fails()) {
            $exception = new ValidateException();
            $exception->setMessageBag($validator->errors());

            throw $exception;
        }
    }

}
