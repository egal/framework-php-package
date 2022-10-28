<?php

namespace Egal\Model\Traits;

use Egal\Model\Exceptions\ValidateException;
use Exception;
use Illuminate\Support\Facades\Validator;

trait HasRelationships
{

    /**
     * @throws Exception|ValidateException
     */
    public function saveRelation(string $name, mixed $value)
    {
        $meta = $this->getModelMetadata();
        $relation = $meta->getRelation($name);
        if ($relation->isGuarded()) throw new Exception('Relation save is not available, is guarded!');

        $saver = $relation->getSaver();

        $validator = Validator::make(['value' => $value], $saver->getValueValidationRules());

        if ($validator->fails()) {
            $exception = new ValidateException();
            $exception->setMessageBag($validator->errors());

            throw $exception;
        }

        call_user_func($saver->getCallback(), $this, $value);
    }

}
