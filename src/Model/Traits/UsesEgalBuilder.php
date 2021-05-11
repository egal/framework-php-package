<?php

namespace EgalFramework\Model\Traits;

use EgalFramework\Model\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use EgalFramework\Model\Builder as EgalBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

trait UsesEgalBuilder
{

    /**
     * Переопределяем EloquentBuilder на EgalBuilder
     *
     * @param QueryBuilder $query
     * @return EgalBuilder
     */
    public function newEloquentBuilder($query)
    {
        return new EgalBuilder($query);
    }

    /**
     * @return Builder|EloquentBuilder
     */
    public static function query()
    {
        return parent::query();
    }

}