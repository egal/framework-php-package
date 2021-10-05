<?php

namespace Egal\Centrifugo;

use Egal\Model\Model;
use phpcent\Client;

trait CenrifugoPublishable
{

    public function getChannelNames(): array
    {
        $service = config('app.name');
        $model = get_class($this->getEntity());
        $modelId = $this->getEntity()->getKey();
        $event = $this->getName();

        return [
            $service,
            $service . '@' . $model . '.' . $modelId,
            $service . '@' . $model . '.' . $event,
            $service . '@' . $model . '.' . $event . '.' . $modelId,
            $service . '@' . $model
        ];
    }

    public function getMessage(): array
    {
        return [
            'type' => 'model_event',
            'data' => [
                'name' => $this->getName(),
                'model_name' => get_class_short_name($this->getEntity()),
                'model_id' => $this->getEntity()->getKey()
            ]
        ];
    }

    private function getName(): string
    {
        return $this->name ?? snake_case(get_class_short_name($this));
    }

    private function getEntity(): Model
    {
        return $this->entity;
    }

    /**
     * @throws CentrifugoPublishException
     */
    public function publish()
    {
        $client = app(Client::class);
        try {
            $client->broadcast($this->getChannelNames(), $this->getMessage());
        } catch (CentrifugoPublishException $exception) {
            throw new CentrifugoPublishException('ERROR: Centrifuge publish throws with exception', [$exception]);
        }
    }

}
