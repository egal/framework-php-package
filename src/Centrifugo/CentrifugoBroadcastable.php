<?php

declare(strict_types=1);

namespace Egal\Centrifugo;

use Egal\Model\Model;

trait CentrifugoBroadcastable
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

        $entity = $this->getEntity();

        if (isset($entity)) {
            $modelName = get_class_short_name($entity);
            $channelNames[] = $service . '@' . $modelName . '.' . $eventName;
            $channelNames[] = $service . '@' . $modelName;
            $modelId = $entity->getKey();

            if (isset($modelId)) {
                $channelNames[] = $service . '@' . $modelName . '.' . $modelId . '.' . $eventName;
                $channelNames[] = $service . '@' . $modelName . '.' . $modelId;
            }
        }

        return $channelNames;
    }

    public function broadcastWith(): array
    {
        $entity = $this->getEntity();

        return isset($entity)
            ? [
                'type' => 'model_event',
                'data' => [
                    'name' => $this->getName(),
                    'model_name' => get_class_short_name($entity),
                    'model_id' => $entity->getKey(),
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

    private function getEntity(): ?Model
    {
        return $this->entity ?? null;
    }

}
