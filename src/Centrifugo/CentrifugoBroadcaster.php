<?php

namespace Egal\Centrifugo;

use Exception;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use phpcent\Client;

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
        $client = app('CentrifugoClient');

        try {
            $client->broadcast($channels, $payload);
        } catch (Exception $exception) {
            throw CentrifugoPublishException::make($exception->getMessage());
        }
    }
}
