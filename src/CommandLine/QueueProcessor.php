<?php

namespace EgalFramework\CommandLine;

use EgalFramework\CommandLine\Exceptions\AccessViolationException;
use EgalFramework\Common\AuthUserType;
use EgalFramework\Common\Interfaces\APIContainer\MethodInterface;
use EgalFramework\Common\Interfaces\Kerberos\MandateInterface;
use EgalFramework\Common\Interfaces\MessageInterface;
use EgalFramework\Common\Queue\Message;
use EgalFramework\Common\RoleManager;
use EgalFramework\Common\Session;
use EgalFramework\Common\Settings;
use EgalFramework\Kerberos\Crypt;
use Exception;
use Illuminate\Support\Facades\Log;
use ReflectionException;
use ReflectionMethod;

class QueueProcessor
{

    private string $queueName;

    private ActionProcessor $actionProcessor;

    public function __construct(string $queueName)
    {
        $this->queueName = $queueName;
        $this->actionProcessor = new ActionProcessor;
    }

    /**
     * @throws ReflectionException
     */
    public function run(): void
    {
        $closure = (new ReflectionMethod(get_class($this), 'processMessage'))->getClosure($this);
        Session::getQueue()->listen(Settings::getAppName(), $this->queueName, $closure);
    }

    /**
     * There is a magic call
     * @param string $data
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function processMessage(string $data)
    {
        $startTime = microtime(TRUE);
        try {
            $message = Session::getQueue()->getMessage($data);
        } catch (Exception $e) {
            Log::warning('Can\'t process message: ' . $e->getMessage() . ' => ' . $data);
            return;
        }
        try {
            $this->checkAccess($message);
            $response = $this->actionProcessor->processMessage($message);
        } catch (Exception $e) {
            $response = Session::getQueue()->getNewMessageInstance();
            $response->setUid($message->getUid());
            $errorData = [
                'uid' => $message->getUid(),
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'error_type' => get_class($e)
                ],
                'data' => $data,
            ];
            if (method_exists($e, 'getErrors')) {
                $errorData['error']['messages'] = $e->getErrors();
            }
            $response->setData($errorData);
            Log::warning($e->getMessage(), $e->getTrace());
        }
        $response->setProcessTime(microtime(TRUE) - $startTime);
        Session::getQueue()->send(Settings::getAppName(), $response->getUid(), $response, 10);
    }

    /**
     * @param MessageInterface $message
     * @throws AccessViolationException
     */
    private function checkAccess(MessageInterface $message)
    {
        Session::setRoleManager(new RoleManager);
        if (Settings::getDisableAuth()) {
            return;
        }
        $method = Session::getApiStorage()->getMethod($message->getModel(), $message->getAction());
        $this->checkMethodExists($message, $method);
        $allowedRoles = $method->getRoles();
        Session::getRoleManager()->setRole('@all');

        $this->setMandateRoles($message);
        $this->checkRoleAccess($allowedRoles, $message);
    }

    /**
     * @param MessageInterface $message
     * @param MethodInterface|null $method
     * @throws AccessViolationException
     */
    private function checkMethodExists(MessageInterface $message, ?MethodInterface $method): void
    {
        if (is_null($method)) {
            throw new AccessViolationException(
                sprintf('Method %s/%s does not exist', $message->getModel(), $message->getAction()),
                404
            );
        }
    }

    /**
     * Set mandate roles from message
     *
     * @param MessageInterface $message
     * @throws AccessViolationException
     */
    private function setMandateRoles(MessageInterface $message): void
    {
        $mandate = $this->getMandateFromMassage($message);
        if (!$mandate) {
            return;
        }

        Session::getRegistry()->set('User', $mandate->getData()->getUser());
        Session::getRoleManager()->setRole('@logged');
        Session::getRoleManager()->setRoles(
            array_merge(Session::getRoleManager()->getRoles(), $mandate->getData()->getRoles())
        );

        if (!empty($mandate->getData()->getUser()['type'])
            && ($mandate->getData()->getUser()['type'] == AuthUserType::SERVICE)
        ) {
            Session::getRoleManager()->setRole('@service');
        }
    }

    /**
     * @param MessageInterface $message
     * @return ?MandateInterface
     * @throws AccessViolationException
     * @throws Exception
     */
    private function getMandateFromMassage(MessageInterface $message): ?MandateInterface
    {
        $data = $message->getMandate();
        if (empty($data)) {
            return null;
        }
        json_decode($data);
        if (json_last_error()) {
            throw new AccessViolationException('Incorrect token: ' . json_last_error_msg(), 401);
        }

        if (Settings::getIsAuth() && Session::getMessage() && $message->getSender() !== 'web') {
            $modelName = Session::getModelManager()->getModelPath('Service');
            $model = new $modelName;
            $mandate = $model->getMandate($data);
            if (empty($mandate)) {
                return null;
            }

            $sender = Session::getMessage()->getSender();
            $service = $model->where('name', $sender)->first();
            if (!$service) {
                return null;
            }
            $password = (new Crypt)->decrypt($service->password, Settings::getAppKey());

            return Session::getKerberosApi()->getMandate($mandate, $password);
        } else {
            $request = Session::getSendRequest();
            $message = $request->createMessage('Service', 'getMandate');
            $message->setData([$data]);
            $request->send(env('AUTH_SERVICE_NAME'), $message);
            $response = $request->read(env('AUTH_SERVICE_NAME'), $message->getUid());
            if ($response->getData() == false) {
                throw new AccessViolationException('Please, re-login', 401);
            }
            $this->processAuthRequestError($response);
            return Session::getKerberosApi()->getMandate($response->getData(), Settings::getAppKey());
        }
    }

    /**
     * @param MessageInterface $response
     * @throws Exception
     */
    private function processAuthRequestError(MessageInterface $response): void
    {
        $data = $response->getData();
        if (is_string($data)) {
            return;
        }
        if (!is_array($data)) {
            throw new Exception(sprintf('Unknown data type from auth server: %s', var_export($data, true)), 500);
        }
        if (!isset($data['error'])) {
            throw new Exception(sprintf('Incorrect message type from auth server: %s', json_encode($data)), 500);
        }
        throw new Exception($data['error']['message'], $data['error']['code']);
    }

    /**
     * @param array $allowedRoles
     * @param MessageInterface $message
     * @throws AccessViolationException
     */
    private function checkRoleAccess(array $allowedRoles, MessageInterface $message): void
    {
        if (Session::getRoleManager()->hasRoles($allowedRoles)) {
            return;
        }

        throw new AccessViolationException(
            sprintf(
                'Access denied to %s/%s',
                $message->getModel(),
                $message->getAction()
            ) . (
            Settings::getDebugMode()
                ? ' roles: ' . implode(', ', Session::getRoleManager()->getRoles())
                . ' allowed roles: ' . implode(', ', $allowedRoles)
                : ''
            ),
            401
        );
    }

}
