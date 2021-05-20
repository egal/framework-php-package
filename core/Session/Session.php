<?php

namespace Egal\Core\Session;

use Egal\Auth\Accesses\StatusAccess;
use Egal\Auth\Exceptions\TokenExpiredException;
use Egal\Auth\Tokens\UserServiceToken;
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

    private static function getSingleton(): Session
    {
        return app(self::class);
    }

    public static function isActionMessageExists(): bool
    {
        return !is_null(self::getSingleton()->actionMessage);
    }

    public static function isUserServiceTokenExists(): bool
    {
        return !is_null(self::getSingleton()->userServiceToken);
    }

    public static function isAuthEnabled(): bool
    {
        return config('auth.enabled');
    }

    /**
     * @throws Exception
     */
    public static function userServiceTokenExistsOrFail(): void
    {
        if (!self::isUserServiceTokenExists()) {
            throw new CurrentSessionException('The current Session does not contain UST!');
        }
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
     * @return UserServiceToken
     * @throws Exception
     */
    public static function getUserServiceToken(): UserServiceToken
    {
        self::userServiceTokenExistsOrFail();
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
     * @param ActionMessage $actionMessage
     * @throws TokenSignatureInvalidException
     */
    public static function setActionMessage(ActionMessage $actionMessage): void
    {
        self::getSingleton()->actionMessage = $actionMessage;
        if ($actionMessage->isTokenExist()) {
            $encodedToken = $actionMessage->getToken();
            try {
                $ust = UserServiceToken::fromJWT($encodedToken, config('app.service_key'));
            } catch (SignatureInvalidException $exception) {
                throw new TokenSignatureInvalidException();
            }
            self::setUserServiceToken($ust);
        }
    }

    /**
     * @param UserServiceToken $userServiceToken
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
        self::getSingleton()->actionMessage = null;
    }

    public static function unsetUserServiceToken(): void
    {
        self::getSingleton()->userServiceToken = null;
    }

}
