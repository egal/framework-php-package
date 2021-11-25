<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class ReplyMessageNotBelongToRequestException extends Exception
{

    protected $message = 'The reply message does not belong to this request!';

    protected $code = 500;

}
