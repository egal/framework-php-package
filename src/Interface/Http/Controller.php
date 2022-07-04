<?php

namespace Egal\Interface\Http;

use Egal\Core\Exceptions\HasData;
use Egal\Core\Exceptions\HasInternalCode;
use Egal\Interface\Facades\Manager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class Controller
{
    public function show(Request $request, $label)
    {
        try {
            Log::debug('components', Manager::getComponents());
            $component = Manager::getComponentByLabel($label);
        } catch (Exception $exception) {
            $exceptionResponseData = [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'internal_code' => $exception instanceof HasInternalCode ? $exception->getInternalCode() : null,
                'data' => $exception instanceof HasData ? $exception->getData() : null
            ];
        }

        return response()->json([
            'message' => null,
            'data' => isset($component) ? $component->toArray() : null,
            'exception' => $exceptionResponseData ?? null
        ])->setStatusCode(isset($exceptionResponseData) ? $exceptionResponseData['code'] : Response::HTTP_OK);
    }

}
