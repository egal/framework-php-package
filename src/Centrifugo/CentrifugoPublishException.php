<?php

declare(strict_types=1);

namespace Egal\Centrifugo;

use Exception;

class CentrifugoPublishException extends Exception
{

    protected $message = 'Centrifuge publish throws with exception!';

    protected $code = 500;

}
