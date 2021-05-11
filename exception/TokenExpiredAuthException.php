<?php

namespace Egal\Exception;

class TokenExpiredAuthException extends AuthException
{

    const BASE_MESSAGE_LINE = 'Срок действия токена истек!';

}
