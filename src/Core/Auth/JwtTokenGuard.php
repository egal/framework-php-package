<?php

namespace Egal\Core\Auth;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JwtTokenGuard implements Guard
{
    use GuardHelpers;
    private Request $request;

    public function __construct (Request $request) {
        $this->request = $request;
    }

    public function user()
    {
        switch ($this->request->header('AuthorizationType')) {
            case AuthorizationType::Cookie->value:
                $token = $this->request->session()->get('access_token');
                break;
            case AuthorizationType::Header->value:
                $token = $this->request->header('Authorization');
                break;
            default:
                // TODO отдельный exception
                throw new Exception("Not specified correct 'Authorization-Type' header!", Response::HTTP_BAD_REQUEST);
        }

        if ($token === null) {
            $this->user = null;
            return $this->user;
        }

        // TODO проверка времени жизни токена

        $decoded = JWT::decode($token, new Key(config('auth.public_key'), 'RS256'));

        if ($decoded->typ !== 'access') {
            throw new Exception('Invalid token type!');
        }

        $userModel = null;

        if ($userModelClass = config('auth.user_model_class', User::class)) {
            $userModel = new $userModelClass();

            if (!($userModel instanceof UserModelInterface)) {
                throw new Exception('Error! User model class must be implements of ' . UserModelInterface::class . '!');
            }

            $userModel = $userModel->findById($decoded->id);
        }

        $this->user = $userModel;

        return $this->user;
    }

    public function validate(array $credentials = [])
    {
        // TODO: Implement validate() method.
    }
}
