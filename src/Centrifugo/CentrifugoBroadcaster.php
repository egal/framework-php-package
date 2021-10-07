<?php

declare(strict_types=1);

namespace Egal\Centrifugo;

use Exception;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;

class CentrifugoBroadcaster extends Broadcaster
{

    public function auth($request): void
    {
        // TODO: Implement auth() method.
    }

    public function validAuthenticationResponse($request, $result): void
    {
        // TODO: Implement validAuthenticationResponse() method.
    }

    /**
     * @throws \Egal\Centrifugo\CentrifugoPublishException
     */
    public function broadcast(array $channels, $event, array $payload = []): void
    {
        try {
            Centrifugo::getClient()->broadcast($channels, $payload);
        } catch (Exception $exception) {
            throw CentrifugoPublishException::make($exception->getMessage());
        }
    }

}
