<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Egal\Core\Bus\Bus;
use Egal\Core\Communication\Response;
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
                    $actionErrorMessage->setInternalCode(Response::INTERNAL_SERVER_ERROR_STATUS_CODE);
                    break;
                default:
                    if ($exception instanceof HasInternalCode) {
                        $actionErrorMessage->setInternalCode($exception->getInternalCode());

                    } else {
                        $actionErrorMessage->setInternalCode(Response::INTERNAL_SERVER_ERROR_STATUS_CODE);
                    }
                    break;
            }

            $actionErrorMessage->setActionMessage(Session::getActionMessage());
            Bus::getInstance()->publishMessage($actionErrorMessage);
        }

        parent::report($exception);
    }

}
