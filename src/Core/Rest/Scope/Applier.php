<?php

namespace Egal\Core\Rest\Scope;

use Illuminate\Database\Eloquent\Builder;

class Applier
{

    public static function apply(Builder $query, array $scopeObjects)
    {
        $scopes = [];
        foreach ($scopeObjects as $scopeObject) {
            $scope = $scopeObject->getName();
            $parameters = $scopeObject->getParameters();
            $scopes[$scope] = array_column($parameters,'value');
            $query->scopes($scopes);
        }

        return $query;
    }
}
