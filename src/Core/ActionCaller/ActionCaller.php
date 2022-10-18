<?php

declare(strict_types=1);

namespace Egal\Core\ActionCaller;

use Egal\Core\Exceptions\ActionParameterValidateException;
use Egal\Core\Exceptions\NoAccessActionCallException;
use Egal\Core\Session\Session;
use Egal\Model\Facades\ModelMetadataManager;
use Egal\Model\Metadata\ActionMetadata;
use Egal\Model\Metadata\ActionParameterMetadata;
use Egal\Model\Metadata\ModelMetadata;
use Illuminate\Support\Facades\Validator;

/**
 * Class ActionCaller.
 *
 * Designed to call Actions on Models.
 */
class ActionCaller
{

    /**
     * Parameters of the called action.
     *
     * @var mixed
     */
    protected array $actionParameters = [];

    /**
     * Model Metadata for which Action is called.
     */
    private ModelMetadata $modelMetadata;

    /**
     * Model Action Metadata for which Action is called.
     */
    private ActionMetadata $modelActionMetadata;

    /**
     * ActionCaller constructor.
     *
     * @throws \Exception
     */
    public function __construct(string $modelName, string $actionName, array $actionParameters = [])
    {
        $this->modelMetadata = ModelMetadataManager::getModelMetadata($modelName);
        $this->modelActionMetadata = $this->modelMetadata->getAction($actionName);
        $this->actionParameters = $actionParameters;
    }

    /**
     * Calling action.
     *
     * @throws \Exception
     */
    public function call(): mixed
    {
        return call_user_func_array(
            [
                $this->modelMetadata->getModelClass(),
                $this->modelActionMetadata->getMethodName(),
            ],
            $this->getValidActionParameters()
        );
    }

    /**
     * Generates valid parameters based on {@see \Egal\Core\ActionCaller\ActionCaller::modelActionMetadata}.
     *
     * If it is impossible to generate valid parameters, an exception is thrown.
     *
     * @throws ActionParameterValidateException
     */
    private function getValidActionParameters(): array
    {
        $actionParameters = $this->actionParameters;

        $notAllowedParameters = array_filter(
            array_keys($actionParameters),
            fn ($actionParameter) => !$this->modelActionMetadata->parameterExist($actionParameter)
        );

        /** @var ActionParameterMetadata $parameter */
        foreach ($this->modelActionMetadata->getParameters() as $parameterMetadata) {
            if (array_key_exists($parameterMetadata->getName(), $actionParameters)
                || $parameterMetadata->getDefault() === null
            ) {
                continue;
            }

            $actionParameters[$parameter->getName()] = $parameter->getDefault();
        }

        $validator = Validator::make($actionParameters, $this->modelActionMetadata->getValidationRules());

        if ($validator->fails() || $notAllowedParameters !== []) {
            $exception = new ActionParameterValidateException();
            $exception->setMessageBag($validator->errors());

            foreach ($notAllowedParameters as $parameter) {
                $exception->mergeMessage("Parameter $parameter not allowed here!");
            }

            throw $exception;
        }

        return $actionParameters;
    }

}
