<?php

namespace Egal\Core\Session;

use Egal\Auth\Accesses\StatusAccess;
use Egal\Auth\Exceptions\UndefinedTokenTypeException;
use Egal\Auth\Tokens\ServiceServiceToken;
use Egal\Auth\Tokens\Token;
use Egal\Auth\Tokens\TokenType;
use Egal\Auth\Tokens\UserServiceToken;
use Egal\Core\Events\ServiceServiceTokenDetectedEvent;
use Egal\Core\Events\UserServiceTokenDetectedEvent;
use Egal\Core\Exceptions\CurrentSessionException;
use Egal\Core\Messages\ActionMessage;
use Egal\Exception\AuthException;
use Egal\Exception\TokenExpiredAuthException;
use Exception;
use Firebase\JWT\SignatureInvalidException;

final class Session
{

    private ?ActionMessage $actionMessage = null;
    private ?UserServiceToken $userServiceToken = null;
    private ?ServiceServiceToken $serviceServiceToken = null;

    private static function getSingleton(): Session
    {
        return app(self::class);
    }

    public static function isActionMessageExists(): bool
    {
        return !is_null(self::getSingleton()->actionMessage);
    }

    public static function isAuthEnabled(): bool
    {
        return config('auth.enabled');
    }

    /**
     * @return UserServiceToken
     * @throws Exception
     */
    public static function getUserServiceToken(): UserServiceToken
    {
        if (!self::isUserServiceTokenExists()) {
            throw new CurrentSessionException('The current Session does not contain UST!');
        }

        return self::getSingleton()->userServiceToken;
    }

    public static function getAuthStatus(): string
    {
        if (Session::isUserServiceTokenExists()) {
            return StatusAccess::LOGGED;
        } else {
            return StatusAccess::GUEST;
        }
    }

    public static function isUserServiceTokenExists(): bool
    {
        return !is_null(self::getSingleton()->userServiceToken);
    }

    public static function getServiceServiceToken(): ServiceServiceToken
    {
        if (!self::isServiceServiceTokenExists()) {
            throw new CurrentSessionException('The current Session does not contain SST!');
        }

        return self::getSingleton()->serviceServiceToken;
    }

    public static function isServiceServiceTokenExists(): bool
    {
        return !is_null(self::getSingleton()->serviceServiceToken);
    }

    /**
     * @return ActionMessage
     * @throws Exception
     */
    public static function getActionMessage(): ActionMessage
    {
        self::actionMessageExistsOrFail();
        return self::getSingleton()->actionMessage;
    }

    /**
     * @throws Exception
     */
    public static function actionMessageExistsOrFail(): void
    {
        if (!self::isActionMessageExists()) {
            throw new CurrentSessionException('The current Session does not contain ActionMessage!');
        }
    }

    /**
     * @param ActionMessage $actionMessage
     * @throws AuthException
     * @throws TokenExpiredAuthException
     * @throws UndefinedTokenTypeException
     * @throws \Egal\Auth\Exceptions\InitializeServiceServiceTokenException
     * @throws \Egal\Auth\Exceptions\InitializeUserServiceTokenException
     */
    public static function setActionMessage(ActionMessage $actionMessage): void
    {
        self::getSingleton()->actionMessage = $actionMessage;
        if (!$actionMessage->isTokenExist()) {
            return;
        }

        try {
            self::setToken($actionMessage->getToken());
        } catch (Exception $exception) {
            if ($exception instanceof SignatureInvalidException) {
                throw new AuthException('Токен не прошел проверку подписи!');
            } else {
                throw $exception;
            }
        }
    }

    /**
     * @param string $encodedToken
     * @throws TokenExpiredAuthException
     * @throws \Egal\Auth\Exceptions\InitializeServiceServiceTokenException
     * @throws \Egal\Auth\Exceptions\InitializeUserServiceTokenException
     * @throws UndefinedTokenTypeException
     */
    private static function setToken(string $encodedToken): void
    {
        $decodedToken = Token::decode($encodedToken, config('app.service_key'));

        switch ($decodedToken['type']) {
            case TokenType::USER_SERVICE:
                self::setUserServiceToken(UserServiceToken::fromArray($decodedToken));
                break;
            case TokenType::SERVICE_SERVICE:
                self::setServiceServiceToken(ServiceServiceToken::fromArray($decodedToken));
                break;
            default:
                throw new UndefinedTokenTypeException();
        }
    }

    /**
     * @param ServiceServiceToken $serviceServiceToken
     * @throws TokenExpiredAuthException
     */
    public static function setServiceServiceToken(ServiceServiceToken $serviceServiceToken): void
    {
        $serviceServiceToken->isAliveOrFail();
        self::getSingleton()->serviceServiceToken = $serviceServiceToken;
        event(new ServiceServiceTokenDetectedEvent());
    }

    /**
     * @param UserServiceToken $userServiceToken
     * @throws TokenExpiredAuthException
     */
    public static function setUserServiceToken(UserServiceToken $userServiceToken): void
    {
        $userServiceToken->isAliveOrFail();
        self::getSingleton()->userServiceToken = $userServiceToken;
        event(new UserServiceTokenDetectedEvent());
    }

    public static function unsetActionMessage(): void
    {
        self::unsetUserServiceToken();
        self::unsetServiceServiceToken();
        self::getSingleton()->actionMessage = null;
    }

    public static function unsetUserServiceToken(): void
    {
        self::getSingleton()->userServiceToken = null;
    }

    public static function unsetServiceServiceToken(): void
    {
        self::getSingleton()->serviceServiceToken = null;
    }

}
