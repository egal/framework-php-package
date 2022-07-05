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
    // TODO обновление токена
    public function register(Request $request)
    {
        try {
            // TODO должно из метаданных подтягиваться
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                $exception = new ValidateException();
                $exception->setMessageBag($validator->errors());

                throw $exception;
            }

            $request['password'] = Hash::make($request['password']);
            User::query()->create(['email' => $request['email'], 'password' => $request['password']]);

            return response()->setStatusCode(Response::HTTP_OK);
        } catch (Exception $exception) {

            return response()->json($this->getExceptionResponseData($exception))->setStatusCode($exception->getCode());
        }
    }

    public function login(Request $request)
    {
        try {
            // TODO должно из метаданных подтягиваться
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                $exception = new ValidateException();
                $exception->setMessageBag($validator->errors());

                throw $exception;
            }

            Auth::attempt(['email' => $request['email'], 'password' => $request['password']]);
            /** @var User $user */
            $user = User::query()->where('email', $request['email'])->first();
            if ($user) {

                if (Hash::check($request['password'], $user->password)) {
                    $accessToken = $user->makeAccessToken();
                    $refreshToken = $user->makeRefreshToken();
                } else {
                    // TODO отдельный exception
                    throw new Exception("Password mismatch", Response::HTTP_BAD_REQUEST);
                }

            } else {
                // TODO отдельный exception
                throw new Exception("User does not exist", Response::HTTP_BAD_REQUEST);
            }

            switch ($request->header('Authorization-Type')) {
                case AuthorizationType::Cookie:
                    $request->session()->put('access_token', $accessToken);
                    $request->session()->put('refresh_token', $refreshToken);

                    return response()->json()->setStatusCode(Response::HTTP_OK);
                case AuthorizationType::Header:
                    $tokens = ['access_token' => $accessToken, 'refresh_token' => $refreshToken];

                    return response()->json($tokens)->setStatusCode(Response::HTTP_OK);
                default:
                    // TODO отдельный exception
                    throw new Exception("Not specified header 'Authorization-Type'!", Response::HTTP_BAD_REQUEST);
            }
        } catch (Exception $exception) {
            return response()->json($this->getExceptionResponseData($exception))->setStatusCode($exception->getCode());
        }
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
