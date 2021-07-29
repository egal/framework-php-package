<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

use Egal\Model\Builder;

/**
 * Trait UsesBuilder
 *
 * @package Egal\Model\Traits
 * @mixin \Egal\Model\Model
 */
trait UsesBuilder
{

    /**
     * Create a new Egal query builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Egal\Model\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * @return \Egal\Model\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function newQuery()
    {
        if ($this->isInstanceForAction) {
            return $this->newQueryForAction();
        }

        return parent::newQuery();
    }

    /**
     * @return \Egal\Model\Builder|\Illuminate\Database\Eloquent\Builder
     */
    public function newQueryForAction()
    {
        return parent::newQuery();
    }

}
