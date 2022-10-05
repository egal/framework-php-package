<?php

declare(strict_types=1);

namespace Egal\Core\Session;

use Egal\Auth\Accesses\StatusAccess;
use Egal\Auth\Exceptions\UndefinedTokenTypeException;
use Egal\Auth\Exceptions\WrongTokenTypeException;
use Egal\Auth\Tokens\ServiceServiceToken;
use Egal\Auth\Tokens\Token;
use Egal\Auth\Tokens\TokenType;
use Egal\Auth\Tokens\UserServiceToken;
use Egal\Core\Events\ServiceServiceTokenDetectedEvent;
use Egal\Core\Events\UserServiceTokenDetectedEvent;
use Egal\Core\Exceptions\CurrentSessionException;
use Egal\Core\Exceptions\UnableDecodeTokenException;
use Egal\Core\Messages\ActionMessage;
use Exception;

final class Session
{

    private ?ActionMessage $actionMessage = null;

    private ?UserServiceToken $userServiceToken = null;

    private ?ServiceServiceToken $serviceServiceToken = null;

    public static function isActionMessageExists(): bool
    {
        return self::getSingleton()->actionMessage !== null;
    }

    public static function isAuthEnabled(): bool
    {
        return config('auth.enabled');
    }

    public static function getUserServiceToken(): UserServiceToken
    {
        self::isUserServiceTokenExistsOrFail();

        return self::getSingleton()->userServiceToken;
    }

    public static function isUserServiceTokenExistsOrFail(): bool
    {
        if (!self::isUserServiceTokenExists()) {
            throw new CurrentSessionException('The current Session does not contain UST!');
        }

        return true;
    }

    public static function getAuthStatus(): string
    {
        return self::isUserServiceTokenExists() || self::isServiceServiceTokenExists()
            ? StatusAccess::LOGGED
            : StatusAccess::GUEST;
    }

    public static function isUserServiceTokenExists(): bool
    {
        return self::getSingleton()->userServiceToken !== null;
    }

    public static function getServiceServiceToken(): ServiceServiceToken
    {
        self::isServiceServiceTokenExistsOrFail();

        return self::getSingleton()->serviceServiceToken;
    }

    public static function isServiceServiceTokenExistsOrFail(): bool
    {
        if (!self::isServiceServiceTokenExists()) {
            throw new CurrentSessionException('The current Session does not contain SST!');
        }

        return true;
    }

    public static function isServiceServiceTokenExists(): bool
    {
        return self::getSingleton()->serviceServiceToken !== null;
    }

    public static function getActionMessage(): ActionMessage
    {
        self::isActionMessageExistsOrFail();

        return self::getSingleton()->actionMessage;
    }

    public static function isActionMessageExistsOrFail(): bool
    {
        if (!self::isActionMessageExists()) {
            throw new CurrentSessionException('The current Session does not contain ActionMessage!');
        }

        return true;
    }

    public static function setActionMessage(ActionMessage $actionMessage): void
    {
        self::unsetActionMessage();
        self::getSingleton()->actionMessage = $actionMessage;

        if (!$actionMessage->isTokenExist()) {
            return;
        }

        self::setToken($actionMessage->getToken());
    }

    public static function setServiceServiceToken(ServiceServiceToken $serviceServiceToken): void
    {
        $serviceServiceToken->isAliveOrFail();
        self::getSingleton()->serviceServiceToken = $serviceServiceToken;
        event(new ServiceServiceTokenDetectedEvent());
    }

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

    private static function getSingleton(): Session
    {
        return app(self::class);
    }

    private static function setToken(string $encodedToken): void
    {
        try {
            $decodedToken = Token::decode($encodedToken, config('app.service_key'));
        } catch (Exception $exception) {
            throw config('app.debug')
                ? $exception
                : new UnableDecodeTokenException();
        }
        
        if (!isset($decodedToken['type'])) {
            throw new UndefinedTokenTypeException();
        }

        switch ($decodedToken['type']) {
            case TokenType::USER_SERVICE:
                self::setUserServiceToken(UserServiceToken::fromArray($decodedToken));
                break;
            case TokenType::SERVICE_SERVICE:
                self::setServiceServiceToken(ServiceServiceToken::fromArray($decodedToken));
                break;
            default:
                throw new WrongTokenTypeException();
        }
    }

}
