<?php

namespace Egal\Core\Session;

use Egal\Auth\Accesses\StatusAccess;
use Egal\Auth\Exceptions\InitializeServiceServiceTokenException;
use Egal\Auth\Exceptions\InitializeUserServiceTokenException;
use Egal\Auth\Exceptions\TokenExpiredException;
use Egal\Auth\Exceptions\UndefinedTokenTypeException;
use Egal\Auth\Tokens\ServiceServiceToken;
use Egal\Auth\Tokens\Token;
use Egal\Auth\Tokens\TokenType;
use Egal\Auth\Tokens\UserServiceToken;
use Egal\Core\Events\ServiceServiceTokenDetectedEvent;
use Egal\Core\Events\UserServiceTokenDetectedEvent;
use Egal\Core\Exceptions\CurrentSessionException;
use Egal\Core\Exceptions\TokenSignatureInvalidException;
use Egal\Core\Messages\ActionMessage;
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
        self::isUserServiceTokenExistsOrFail();
        return self::getSingleton()->userServiceToken;
    }

    /**
     * @return bool
     * @throws CurrentSessionException
     */
    public static function isUserServiceTokenExistsOrFail(): bool
    {
        if (!self::isUserServiceTokenExists()) {
            throw new CurrentSessionException('The current Session does not contain UST!');
        }
        return true;
    }

    /**
     * @return bool
     * @throws CurrentSessionException
     * @deprecated since v2.0.0, use {@see Session::isUserServiceTokenExistsOrFail()}.
     */
    public static function userServiceTokenExistsOrFail(): bool
    {
        return self::isUserServiceTokenExistsOrFail();
    }

    /**
     * Return auth status for user.
     *
     * @return string
     */
    public static function getAuthStatus(): string
    {
        if (Session::isUserServiceTokenExists() || Session::isServiceServiceTokenExists()) {
            return StatusAccess::LOGGED;
        } else {
            return StatusAccess::GUEST;
        }
    }

    public static function isUserServiceTokenExists(): bool
    {
        return !is_null(self::getSingleton()->userServiceToken);
    }

    /**
     * @return ServiceServiceToken
     * @throws CurrentSessionException
     */
    public static function getServiceServiceToken(): ServiceServiceToken
    {
        self::isServiceServiceTokenExistsOrFail();
        return self::getSingleton()->serviceServiceToken;
    }

    /**
     * @return bool
     * @throws CurrentSessionException
     */
    public static function isServiceServiceTokenExistsOrFail(): bool
    {
        if (!self::isServiceServiceTokenExists()) {
            throw new CurrentSessionException('The current Session does not contain SST!');
        }

        return true;
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
        self::isActionMessageExistsOrFail();
        return self::getSingleton()->actionMessage;
    }

    /**
     * @return bool
     * @throws CurrentSessionException
     */
    public static function isActionMessageExistsOrFail(): bool
    {
        if (!self::isActionMessageExists()) {
            throw new CurrentSessionException('The current Session does not contain ActionMessage!');
        }
        return true;
    }

    /**
     * @return bool
     * @throws CurrentSessionException
     * @deprecated since v2.0.0, use {@see Session::isActionMessageExistsOrFail()}.
     */
    public static function actionMessageExistsOrFail(): bool
    {
        return self::isActionMessageExistsOrFail();
    }

    /**
     * @param ActionMessage $actionMessage
     * @throws TokenSignatureInvalidException
     * @throws UndefinedTokenTypeException
     * @throws InitializeServiceServiceTokenException
     * @throws InitializeUserServiceTokenException
     * @throws TokenExpiredException
     */
    public static function setActionMessage(ActionMessage $actionMessage): void
    {
        self::getSingleton()->actionMessage = $actionMessage;
        if (!$actionMessage->isTokenExist()) {
            return;
        }

        try {
            self::setToken($actionMessage->getToken());
        } catch (SignatureInvalidException $exception) {
            throw new TokenSignatureInvalidException();
        }
    }

    /**
     * @param string $encodedToken
     * @throws InitializeServiceServiceTokenException
     * @throws InitializeUserServiceTokenException
     * @throws UndefinedTokenTypeException
     * @throws TokenExpiredException
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
     * @throws TokenExpiredException
     */
    public static function setServiceServiceToken(ServiceServiceToken $serviceServiceToken): void
    {
        $serviceServiceToken->isAliveOrFail();
        self::getSingleton()->serviceServiceToken = $serviceServiceToken;
        event(new ServiceServiceTokenDetectedEvent());
    }

    /**
     * @param UserServiceToken $userServiceToken
     * @throws TokenExpiredException
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
