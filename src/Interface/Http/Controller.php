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
            $component = Manager::getComponentByLabel($label);

            return response()->json([
                'data' => $component->toArray()
            ])->setStatusCode(Response::HTTP_OK);
        } catch (Exception $exception) {
            $exceptionResponseData = [
                'message' => $exception->getMessage(),
                'internal_code' => $exception instanceof HasInternalCode ? $exception->getInternalCode() : null,
                'data' => $exception instanceof HasData ? $exception->getData() : null
            ];

            return response()->json([
                'exception' => $exceptionResponseData
            ])->setStatusCode($exception->getCode());
        }
    }

}
