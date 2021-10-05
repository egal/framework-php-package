<?php

namespace Egal\Centrifugo;

use phpcent\Client;

trait CenrifugoPublishable
{
    public function getChannelNames(): array
    {
        $service = config('app.name');
        $event = $this->getName();

        $channelNames = [
            $service,
            $service . '@' . $event
        ];

        if (isset($this->model)) {
            $modelName = get_class_short_name($this->model);
            $modelId = $this->model->getKey();

            $modelChannelNames = [
                $service . '@' . $modelName . '.' . $event,
                $service . '@' . $modelName
            ];

            $channelNames = array_merge($channelNames, $modelChannelNames);
            if (isset($modelId)) {
                $modelIdChannelNames = [
                    $service . '@' . $modelName . '.' . $modelId . '.' . $event,
                    $service . '@' . $modelName . '.' .  $modelId
                ];

                $channelNames = array_merge($channelNames, $modelIdChannelNames);
            }
        }
        return $channelNames;
    }

    public function getMessage(): array
    {
        return isset($this->model)
            ? [
                'type' => 'model_event',
                'data' => [
                    'name' => $this->getName(),
                    'model_name' => get_class_short_name($this->model),
                    'model_id' => $this->model->getKey()
                ]
            ]
            : [
                'type' => 'event',
                'data' => [
                    'name' => $this->getName()
                ]
            ];
    }

    private function getName(): string
    {
        return $this->name ?? snake_case(get_class_short_name($this));
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
