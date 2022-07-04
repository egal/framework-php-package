<?php

namespace Egal\Core\Auth;

use Egal\Core\Exceptions\HasData;
use Egal\Core\Exceptions\HasInternalCode;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TokenController extends BaseController
{
    // TODO обновление токена
    public function register(Request $request)
    {
        // TODO должно из метаданных подтягиваться
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails())
        {
            $exceptionResponseData = $validator->errors()->all();
        } else {
            $request['password']=Hash::make($request['password']);
            /** @var User $user */
            $user = User::create($request->toArray());
            $accessToken = $user->makeAccessToken();
            $refreshToken = $user->makeRefreshToken();
            $tokens = ['access_token' => $accessToken, 'refresh_token' => $refreshToken];
        }

        return response()->json([
            'message' => null,
            'data' => $tokens ?? null,
            'exception' => $exceptionResponseData ?? null
        ])->setStatusCode(isset($exceptionResponseData) ? $exceptionResponseData['code'] : Response::HTTP_OK);

    }

    public function login(Request $request)
    {
        // TODO должно из метаданных подтягиваться
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails())
        {
            $exceptionResponseData['message'] = $validator->errors()->all();
            $exceptionResponseData['code'] = Response::HTTP_BAD_REQUEST;
        } else {
            /** @var User $user */
            $user = User::where('email', $request['email'])->first();
            if ($user) {
                if (Hash::check($request['password'], $user->password)) {
                    $accessToken = $user->makeAccessToken();
                    $refreshToken = $user->makeRefreshToken();
                    $tokens = ['access_token' => $accessToken, 'refresh_token' => $refreshToken];
                } else {
                    $exceptionResponseData['message'] = "Password mismatch";
                    $exceptionResponseData['code'] = Response::HTTP_BAD_REQUEST;
                }
            } else {
                $exceptionResponseData['message'] = "User does not exist";
                $exceptionResponseData['code'] = Response::HTTP_BAD_REQUEST;
            }
        }

        return response()->json([
            'message' => null,
            'data' => $tokens ?? null,
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
