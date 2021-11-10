<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Egal\Core\Bus\Bus;
use Egal\Core\Messages\ActionErrorMessage;
use Egal\Core\Session\Session;
use Egal\Exception\HasInternalCode;
use Illuminate\Database\QueryException;
use Laravel\Lumen\Exceptions\Handler as BaseExceptionHandler;
use Throwable;

class ExceptionHandler extends BaseExceptionHandler
{

    /**
     * @var string[]
     */
    protected $dontReport = [];

    public function report(Throwable $exception)
    {
        if (Session::isActionMessageExists()) {
            $actionErrorMessage = new ActionErrorMessage();
            $actionErrorMessage->setMessage($exception->getMessage());

            switch (get_class($exception)) {
                case QueryException::class:
                    $actionErrorMessage->setCode(500);
                    break;
                default:
                    $actionErrorMessage->setCode($exception->getCode());
                    break;
            }

            $actionErrorMessage->setActionMessage(Session::getActionMessage());

            if ($exception instanceof HasInternalCode) {
                $actionErrorMessage->setInternalCode($exception->getInternalCode());
            }

            Bus::getInstance()->publishMessage($actionErrorMessage);
        }

        parent::report($exception);
    }

}
