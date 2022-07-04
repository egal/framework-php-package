<?php

namespace Egal\Core\Http;

use Egal\Core\Database\Model;
use Egal\Core\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route as IlluminateRoute;

class Route
{

    public function rest(string $modelClass, string|array $policies): void
    {
        /** @var Model $model */
        $model = new $modelClass();
        $metadata = $model->getMetadata();
        $pluralName = $metadata->getPluralName();

        IlluminateRoute::get("/$pluralName", [Controller::class, 'index'])
            ->defaults('model_class', $modelClass);

        IlluminateRoute::get("/$pluralName/{key}", [Controller::class, 'show'])
            ->defaults('model_class', $modelClass);

        IlluminateRoute::post("/$pluralName", [Controller::class, 'create'])
            ->defaults('model_class', $modelClass);

        IlluminateRoute::delete("/$pluralName/{key}", [Controller::class, 'delete'])
            ->defaults('model_class', $modelClass);

        IlluminateRoute::patch("/$pluralName/{key}", [Controller::class, 'update'])
            ->defaults('model_class', $modelClass);

        IlluminateRoute::get("metadata/$pluralName", [Controller::class, 'metadata'])
            ->defaults('model_class', $modelClass);

        if (!is_iterable($policies)) {
            $policies = [$policies];
        }
        foreach ($policies as $policy) {
            Gate::registerPolicy($modelClass, $policy);
        }
    }

}
