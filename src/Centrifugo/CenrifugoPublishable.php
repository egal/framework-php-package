<?php

declare(strict_types=1);

namespace Egal\Centrifugo;

trait CenrifugoPublishable
{

    public string $connection = 'sync';

    public function broadcastOn(): array
    {
        $service = config('app.service_name');
        $eventName = $this->getName();

        $channelNames = [
            $service,
            $service . '@' . $eventName,
        ];

        if (isset($this->model)) {
            $modelName = get_class_short_name($this->model);
            $channelNames[] = $service . '@' . $modelName . '.' . $eventName;
            $channelNames[] = $service . '@' . $modelName;
            $modelId = $this->model->getKey();

            if (isset($modelId)) {
                $channelNames[] = $service . '@' . $modelName . '.' . $modelId . '.' . $eventName;
                $channelNames[] = $service . '@' . $modelName . '.' . $modelId;
            }
        }

        return $channelNames;
    }

    public function broadcastWith(): array
    {
        return isset($this->model)
            ? [
                'type' => 'model_event',
                'data' => [
                    'name' => $this->getName(),
                    'model_name' => get_class_short_name($this->model),
                    'model_id' => $this->model->getKey(),
                ],
            ]
            : [
                'type' => 'event',
                'data' => [
                    'name' => $this->getName(),
                ],
            ];
    }

    public function broadcastConnections(): array
    {
        return ['centrifugo'];
    }

    private function getName(): string
    {
        return $this->name ?? snake_case(get_class_short_name($this));
    }

}
