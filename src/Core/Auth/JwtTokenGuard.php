<?php

namespace Egal\Core\Auth;

use Egal\Core\Facades\AuthManager;
use Exception;
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
        switch ($this->request->header('Authorization-Type')) {
            case AuthorizationType::Cookie->value:
                $token = $this->request->hasSession() ? $this->request->session()->get('access_token') : null;
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

        $accessToken = AccessToken::fromJWT($token);
        $accessToken->isAliveOrFail();

        if ($accessToken->getType() !== 'access') {
            throw new Exception('Invalid token type!');
        }

        $userModel = AuthManager::newUser();

        if (!($userModel instanceof UserModelInterface)) {
            throw new Exception('Error! User model class must be implements of ' . UserModelInterface::class . '!');
        }

        $userModel = $userModel->findById($accessToken->getSub());

        $this->user = $userModel;

        return $this->user;
    }

    public function validate(array $credentials = [])
    {
        // TODO: Implement validate() method.
    }
}
