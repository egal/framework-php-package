<?php

declare(strict_types=1);

namespace Egal\Centrifugo;

trait CenrifugoPublishable
{

    public function broadcastOn(): array
    {
        $service = config('app.name');
        $event = $this->getName();

        $channelNames = [
            $service,
            $service . '@' . $event,
        ];

        if (isset($this->model)) {
            $modelName = get_class_short_name($this->model);
            $modelId = $this->model->getKey();

            $modelChannelNames = [
                $service . '@' . $modelName . '.' . $event,
                $service . '@' . $modelName,
            ];

            $channelNames = array_merge($channelNames, $modelChannelNames);

            if (isset($modelId)) {
                $modelIdChannelNames = [
                    $service . '@' . $modelName . '.' . $modelId . '.' . $event,
                    $service . '@' . $modelName . '.' . $modelId,
                ];

                $channelNames = array_merge($channelNames, $modelIdChannelNames);
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

    private function getName(): string
    {
        return $this->name ?? snake_case(get_class_short_name($this));
    }

}
