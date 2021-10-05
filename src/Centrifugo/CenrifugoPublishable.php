<?php

namespace Egal\Centrifugo;

use Egal\Model\Model;
use phpcent\Client;

trait CenrifugoPublishable
{
    public function __construct(Model $entity)
    {
        $this->entity = $entity;
        $this->name = snake_case(get_class_short_name($this));
    }

    public function getChannelNames(): array
    {
        $service = config('app.name');
        $model = get_class_short_name($this->entity);
        $modelId = $this->entity->getKey();
        $event = $this->name;

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
                'name' => $this->name,
                'model_name' => get_class_short_name($this->entity),
                'model_id' => $this->entity->getKey()
            ]
        ];
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
