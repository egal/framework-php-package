<?php /** @noinspection PhpMissingFieldTypeInspection */

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

    /**
     * Сообщить или записать исключение.
     *
     * Это отличное место для отправки исключений в Sentry, Bugsnag и т. Д.
     *
     * @param Throwable $e
     * @return void
     *
     * @throws Exception
     */
    public function report(Throwable $e)
    {
        if (Session::isActionMessageExists()) {
            $actionErrorMessage = new ActionErrorMessage();

            switch (get_class($e)) {
                case QueryException::class:
                    /** @var QueryException $e */
                    $actionErrorMessage->setMessage($e->getMessage());
                    $actionErrorMessage->setCode(500);
                    break;
                default:
                    $actionErrorMessage->setMessage($e->getMessage());
                    $actionErrorMessage->setCode($e->getCode());
                    break;
            }

            $actionErrorMessage->setActionMessage(Session::getActionMessage());
            Bus::getInstance()->publishMessage($actionErrorMessage);
            Bus::getInstance()->destructEnvironment();
        }
        parent::report($e);
    }

    /**
     * Вывести исключение в HTTP-ответ.
     *
     * @param Request $request
     * @param Throwable $e
     * @return Response|JsonResponse
     *
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        return parent::render($request, $e);
    }

}
