<?php

namespace Egal\Model\Traits;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Egal\Model\Builder as EgalBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * @package Egal\Model
 */
trait UsesEgalBuilder
{

    /**
     * Переопределяем EloquentBuilder на EgalBuilder
     *
     * @param QueryBuilder $query
     * @return EgalBuilder|EloquentBuilder
     * @noinspection PhpUnused
     */
    public function newEloquentBuilder($query)
    {
        return new EgalBuilder($query);
    }

    /**
     * @return EloquentBuilder
     * @noinspection PhpMissingReturnTypeInspection
     */
    public static function query()
    {
        return parent::query();
    }

}
