<?php

namespace Egal\Core\Auth;

use Exception;
use Illuminate\Http\Response;

class RefreshToken extends Token
{
    protected TokenType $type = TokenType::Refresh;

    public static function fromArray(array $array): Token
    {
        $requiredKeys = ['type', 'exp'];
        if (count(array_intersect($requiredKeys, array_keys($array))) !== count($requiredKeys)) {
            // TODO отдельный exception
            throw new Exception('Incomplete token information!', Response::HTTP_BAD_REQUEST);
        }

        $token = new self();
        if (TokenType::Refresh->value !== $array['type']) {
            // TODO отдельный exception
            throw new Exception('Token type mismatch!', Response::HTTP_BAD_REQUEST);
        }

        return $token;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'exp' => $this->exp
        ];
    }
}
