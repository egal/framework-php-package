<?php

namespace Egal\Core\Http;

use Egal\Core\Exceptions\HasData;
use Egal\Core\Exceptions\HasInternalCode;
use Egal\Core\Facades\FilterParser;
use Egal\Core\Facades\Rest;
use Egal\Core\Facades\SelectParser;
use Egal\Core\Facades\ScopeParser;
use Egal\Core\Facades\OrderParser;
use Egal\Core\Rest\Pagination\PaginationParams;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{

    public function index(Request $request, string $modelClass)
    {
        try {
            $pagination = PaginationParams::make($request->get('per_page'), $request->get('page'));

            $scope = ScopeParser::parse($request->get('scope'));
            $filter = FilterParser::parse($request->get('filter'));
            $select = SelectParser::parse($request->get('select'));
            $order = OrderParser::parse($request->get('order'));
            $indexData = Rest::index($modelClass, $pagination, $scope, $filter, $select, $order);
        } catch (Exception $exception) {
            Log::debug($exception->getMessage());
            $exceptionResponseData = $this->getExceptionResponseData($exception);
        }

        return response()->json([
            'message' => null,
            'data' => $indexData ?? null,
            'exception' => $exceptionResponseData ?? null
        ])->setStatusCode(isset($exceptionResponseData) ? $exceptionResponseData['code'] : Response::HTTP_OK);
    }

    public function show(Request $request, $key, string $modelClass)
    {
        try {
            $select = SelectParser::parse($request->get('select'));
            $showData = Rest::show($modelClass, $key, $select);
        } catch (Exception $exception) {
            $exceptionResponseData = $this->getExceptionResponseData($exception);
        }

        return response()->json([
            'message' => null,
            'data' => $showData ?? null,
            'exception' => $exceptionResponseData ?? null
        ])->setStatusCode(isset($exceptionResponseData) ? $exceptionResponseData['code'] : Response::HTTP_OK);
    }

    public function create(Request $request, string $modelClass)
    {
        try {
            if ($request->getContentType() !== 'json') {
                throw new Exception('Unsupported content type!'); # TODO: Replace to additional exception class!
            }

            $attributes = json_decode($request->getContent(), true);
            $createData = Rest::create($modelClass, $attributes);
        } catch (Exception $exception) {
            $exceptionResponseData = $this->getExceptionResponseData($exception);
        }

        return response()->json([
            'message' => isset($exceptionResponseData) ? null : 'Created successfully',
            'data' => $createData ?? null,
            'exception' => $exceptionResponseData ?? null
        ])->setStatusCode(isset($exceptionResponseData) ? $exceptionResponseData['code'] : Response::HTTP_CREATED);
    }

    public function update(Request $request, $key, string $modelClass)
    {
        try {
            if ($request->getContentType() !== 'json') {
                throw new Exception('Unsupported content type!'); # TODO: Replace to additional exception class!
            }

            $attributes = json_decode($request->getContent(), true);
            $updateData = Rest::update($modelClass, $key, $attributes);
        } catch (Exception $exception) {
            $exceptionResponseData = $this->getExceptionResponseData($exception);
        }

        return response()->json([
            'message' => isset($exceptionResponseData) ? null : 'Updated successfully',
            'data' => $updateData ?? null,
            'exception' => $exceptionResponseData ?? null
        ])->setStatusCode(isset($exceptionResponseData) ? $exceptionResponseData['code'] : Response::HTTP_OK);
    }

    public function delete(Request $request, $key, string $modelClass)
    {
        try {
            Rest::delete($modelClass, $key);
        } catch (Exception $exception) {
            $exceptionResponseData = $this->getExceptionResponseData($exception);
        }

        return response()->json([
            'message' => isset($exceptionResponseData) ? null : 'Deleted successfully',
            'data' => null,
            'exception' => $exceptionResponseData ?? null
        ])->setStatusCode(isset($exceptionResponseData) ? $exceptionResponseData['code'] : Response::HTTP_OK);
    }

    public function metadata(Request $request, string $modelClass)
    {
        try {
            $metadata = Rest::metadata($modelClass);
        } catch (Exception $exception) {
            $exceptionResponseData = $this->getExceptionResponseData($exception);
        }

        return response()->json([
            'message' => null,
            'data' => $metadata ?? null,
            'exception' => $exceptionResponseData ?? null
        ])->setStatusCode(isset($exceptionResponseData) ? $exceptionResponseData['code'] : Response::HTTP_OK);
    }

    private function getExceptionResponseData(Exception $exception): array
    {
        return [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'internal_code' => $exception instanceof HasInternalCode ? $exception->getInternalCode() : null,
            'data' => $exception instanceof HasData ? $exception->getData() : null
        ];
    }

}
