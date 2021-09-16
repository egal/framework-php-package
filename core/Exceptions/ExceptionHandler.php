<?php

namespace Egal\Core\Exceptions;

use Egal\Core\Bus\Bus;
use Egal\Core\Messages\ActionErrorMessage;
use Egal\Core\Session\Session;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Exceptions\Handler as BaseExceptionHandler;
use Throwable;

class ExceptionHandler extends BaseExceptionHandler
{

    /**
     * Список типов исключений, о которых не следует сообщать.
     *
     * @var array
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
            Bus::getInstance()->publishMessage($actionErrorMessage);
        }

        parent::report($exception);
    }

}
