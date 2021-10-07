<?php

namespace Egal\Centrifugo;

use Carbon\Carbon;
use Exception;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;

class CentrifugoBroadcaster extends Broadcaster
{

    public function auth($request)
    {
        // TODO: Implement auth() method.
    }

    public function validAuthenticationResponse($request, $result)
    {
        // TODO: Implement validAuthenticationResponse() method.
    }

    /**
     * @throws CentrifugoPublishException
     */
    public function broadcast(array $channels, $event, array $payload = [])
    {
        try {
            Centrifugo::getClient()->broadcast($channels, $payload);
        } catch (Exception $exception) {
            throw CentrifugoPublishException::make($exception->getMessage());
        }
    }
}
