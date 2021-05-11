<?php

namespace EgalFramework\CommandLine;

use EgalFramework\CommandLine\Exceptions\ActionValidateException;
use EgalFramework\Common\HTTP;
use EgalFramework\Common\Interfaces\MessageInterface;
use EgalFramework\Common\Interfaces\ModelInterface;
use EgalFramework\Common\Session;
use EgalFramework\Common\Settings;
use Exception;

/**
 * Class ActionProcessor
 *
 * This class should be tested manually
 *
 * @codeCoverageIgnore
 * @package App
 */
class ActionProcessor
{

    /**
     * @param MessageInterface $message
     * @return MessageInterface
     * @throws ActionValidateException
     * @throws Exception
     */
    public function processMessage(MessageInterface $message)
    {
        $response = clone $message;
        $model = $this->getModel($message);
        $hash = $this->getHash($message);
        $data = $this->tryGetFromCache($message, $hash);
        if (is_null($data)) {
            $this->checkParams($message->getModel(), $message->getAction(), $message->getData());
            Session::setMessage($message);
            $data = call_user_func_array([$model, $message->getAction()], $message->getData());
            $this->putToCache($message, $hash, $data);
        }
        $response->setData($data);
        return $response;
    }

    /**
     * Generate a model class
     * @param MessageInterface $message
     * @return ModelInterface
     * @throws Exception
     */
    private function getModel(MessageInterface $message)
    {
        $modelName = Session::getModelManager()->getModelPath($message->getModel());
        if (!class_exists($modelName)) {
            throw new Exception('Model not found', 404);
        }
        /** @var ModelInterface $model */
        $model = (empty($message->getId()) || !is_subclass_of($modelName, ModelInterface::class))
            ? new $modelName
            : $modelName::find($message->getId());
        if (!$model) {
            $model = new $modelName;
        }
        return $model;
    }

    /**
     * @param MessageInterface $message
     * @return string
     */
    private function getHash(MessageInterface $message)
    {
        $roles = Session::getRoleManager()->getRoles();
        if (empty($roles)) {
            $roles = [];
        }
        return hash(
            'sha256',
            implode('|', array_merge(
                $roles,
                [$message->getModel(),
                    $message->getAction(),
                    $message->getId(),
                    json_encode($message->getQuery()),
                    json_encode($message->getData()),
                ]
            ))
        );
    }

    /**
     * @param MessageInterface $message
     * @param string $hash
     * @return array|null
     */
    private function tryGetFromCache(MessageInterface $message, string $hash)
    {
        if (
            Settings::getDisableCache() ||
            $message->getMethod() != HTTP::METHOD_GET
        ) {
            return null;
        }
        $cacheStore = Session::getRequestCache()->tags([
            $message->getModel(), $message->getModel() . '_' . (int)$message->getId()
        ]);
        if ($cache = $cacheStore->get($hash)) {
            return json_decode($cache, true);
        }
        return null;
    }

    /**
     * @param string $modelName
     * @param string $action
     * @param array $data
     * @throws ActionValidateException
     */
    private function checkParams(string $modelName, string $action, array $data)
    {
        if (!($method = Session::getApiStorage()->getMethod($modelName, $action))) {
            throw new ActionValidateException('Action does not exists', 404);
        }
        $this->validateParamCount($method->arguments, count($data));
        $key = 0;
        foreach ($method->arguments as $argument) {
            if (!isset($data[$key])) {
                break;
            }
            $this->validateArray($key, $argument['type'], $data[$key]);
            $data[$key] = $this->typeCast($argument['type'], $data[$key]);
            $key++;
        }
    }

    /**
     * @param array $args
     * @param int $cnt
     * @throws ActionValidateException
     */
    private function validateParamCount(array $args, int $cnt)
    {
        $requiredCount = 0;
        foreach ($args as $arg) {
            if ($arg['isRequired']) {
                $requiredCount++;
            }
        }
        if ($requiredCount > $cnt) {
            throw new ActionValidateException('Incorrect param count', 400);
        }
    }

    /**
     * @param int $key
     * @param string $type
     * @param mixed $value
     * @throws ActionValidateException
     */
    private function validateArray(int $key, string $type, $value)
    {
        if (is_array($value) && $type != 'array') {
            throw new ActionValidateException(sprintf('Argument %d should be %s type', $key + 1, $type));
        }
        if ($type == 'array' && !is_array($value)) {
            throw new ActionValidateException(sprintf('Argument %d should be an array', $key + 1));
        }
    }

    /**
     * @param string $type
     * @param mixed $value
     * @return mixed
     */
    private function typeCast(string $type, $value)
    {
        switch ($type) {
            case 'string':
                return (string)$value;
            case 'int':
                return (int)$value;
            case 'bool':
                return (bool)$value;
            default:
                return $value;
        }
    }

    /**
     * @param MessageInterface $message
     * @param string $hash
     * @param $data
     */
    private function putToCache(MessageInterface $message, string $hash, $data)
    {
        if ($message->getMethod() != HTTP::METHOD_GET) {
            return;
        }
        $cacheStore = Session::getRequestCache()->tags([
            $message->getModel(), $message->getModel() . '_' . (int)$message->getId()
        ]);
        $cacheStore->put($hash, json_encode($data), 60 * 60 * 24);
    }

}
