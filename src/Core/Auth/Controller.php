<?php

namespace Egal\Core\Auth;

use Egal\Core\Exceptions\HasData;
use Egal\Core\Exceptions\HasInternalCode;
use Egal\Core\Exceptions\ValidateException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
    public function register(Request $request)
    {
        $user = new User();
        $metadata = $user->getMetadata();

        try {
            $validator = Validator::make($request->toArray(), $metadata->getValidationRules());

            if ($validator->fails()) {
                $exception = new ValidateException();
                $exception->setMessageBag($validator->errors());

                throw $exception;
            }

            $user->setAttribute('email', $request['email']);
            $user->setAttribute('password', Hash::make($request['password']));
            $user->save();

            return response()->noContent()->setStatusCode(Response::HTTP_OK);
        } catch (Exception $exception) {

            return response()->json($this->getExceptionResponseData($exception))->setStatusCode($exception->getCode());
        }
    }

    public function login(Request $request)
    {
        $userModel = new User();
        $metadata = $userModel->getMetadata();

        try {
            $validator = Validator::make($request->toArray(), $metadata->getValidationRules());

            if ($validator->fails()) {
                $exception = new ValidateException();
                $exception->setMessageBag($validator->errors());

                throw $exception;
            }

            /** @var User $user */
            $user = User::query()->where('email', $request['email'])->first();
            if ($user) {

                if (Hash::check($request['password'], $user->password)) {
                    $accessToken = AccessToken::fromUser($user)->generateJWT();
                    $refreshToken = (new RefreshToken())->generateJWT();
                } else {
                    // TODO отдельный exception
                    throw new Exception("Password mismatch", Response::HTTP_BAD_REQUEST);
                }

            } else {
                // TODO отдельный exception
                throw new Exception("User does not exist", Response::HTTP_BAD_REQUEST);
            }

            switch ($request->header('Authorization-Type')) {
                case AuthorizationType::Cookie->value:
                    // TODO хранение сессии в redis
                    $request->session()->put('access_token', $accessToken);
                    $request->session()->put('refresh_token', $refreshToken);

                    return response()->noContent()->setStatusCode(Response::HTTP_OK);
                case AuthorizationType::Header->value:
                    $tokens = ['access_token' => $accessToken, 'refresh_token' => $refreshToken];

                    return response()->json($tokens)->setStatusCode(Response::HTTP_OK);
                default:
                    // TODO отдельный exception
                    throw new Exception("Not specified correct 'Authorization-Type' header!", Response::HTTP_BAD_REQUEST);
            }
        } catch (Exception $exception) {
            return response()->json($this->getExceptionResponseData($exception))->setStatusCode($exception->getCode());
        }
    }

    public function refreshToken(Request $request)
    {
        // TODO обновление токена
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
