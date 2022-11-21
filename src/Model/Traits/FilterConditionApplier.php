<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

use Egal\Model\Builder;
use Egal\Model\Filter\FilterCondition;
use Egal\Model\Filter\FilterConditions\SimpleFilterConditionApplier\SimpleFilterConditionApplier;

/**
 * @mixin \Egal\Model\Model
 */
trait FilterConditionApplier
{

    public function applyEqFilterCondition(Builder &$builder, FilterCondition $condition, string $beforeOperator): void
    {
        SimpleFilterConditionApplier::apply($builder, $condition, $beforeOperator);
    }

    public function applyNeFilterCondition(Builder &$builder, FilterCondition $condition, string $beforeOperator): void
    {
        SimpleFilterConditionApplier::apply($builder, $condition, $beforeOperator);
    }

    public function applyGtFilterCondition(Builder &$builder, FilterCondition $condition, string $beforeOperator): void
    {
        SimpleFilterConditionApplier::apply($builder, $condition, $beforeOperator);
    }

    public function applyLtFilterCondition(Builder &$builder, FilterCondition $condition, string $beforeOperator): void
    {
        SimpleFilterConditionApplier::apply($builder, $condition, $beforeOperator);
    }

    public function applyGeFilterCondition(Builder &$builder, FilterCondition $condition, string $beforeOperator): void
    {
        SimpleFilterConditionApplier::apply($builder, $condition, $beforeOperator);
    }

    public function applyLeFilterCondition(Builder &$builder, FilterCondition $condition, string $beforeOperator): void
    {
        SimpleFilterConditionApplier::apply($builder, $condition, $beforeOperator);
    }

    public function applyCoFilterCondition(Builder &$builder, FilterCondition $condition, string $beforeOperator): void
    {
        SimpleFilterConditionApplier::apply($builder, $condition, $beforeOperator);
    }

    public function applySwFilterCondition(Builder &$builder, FilterCondition $condition, string $beforeOperator): void
    {
        SimpleFilterConditionApplier::apply($builder, $condition, $beforeOperator);
    }

    public function applyEwFilterCondition(Builder &$builder, FilterCondition $condition, string $beforeOperator): void
    {
        SimpleFilterConditionApplier::apply($builder, $condition, $beforeOperator);
    }

    public function applyNcFilterCondition(Builder &$builder, FilterCondition $condition, string $beforeOperator): void
    {
        SimpleFilterConditionApplier::apply($builder, $condition, $beforeOperator);
    }

    public function applyEqiFilterCondition(Builder &$builder, FilterCondition $condition, string $beforeOperator): void
    {
        SimpleFilterConditionApplier::apply($builder, $condition, $beforeOperator);
    }

    public function applyNeiFilterCondition(Builder &$builder, FilterCondition $condition, string $beforeOperator): void
    {
        SimpleFilterConditionApplier::apply($builder, $condition, $beforeOperator);
    }

    public function applyCoiFilterCondition(Builder &$builder, FilterCondition $condition, string $beforeOperator): void
    {
        SimpleFilterConditionApplier::apply($builder, $condition, $beforeOperator);
    }

    public function applySwiFilterCondition(Builder &$builder, FilterCondition $condition, string $beforeOperator): void
    {
        SimpleFilterConditionApplier::apply($builder, $condition, $beforeOperator);
    }

    public function applyEwiFilterCondition(Builder &$builder, FilterCondition $condition, string $beforeOperator): void
    {
        SimpleFilterConditionApplier::apply($builder, $condition, $beforeOperator);
    }

    public function applyNciFilterCondition(Builder &$builder, FilterCondition $condition, string $beforeOperator): void
    {
        SimpleFilterConditionApplier::apply($builder, $condition, $beforeOperator);
    }

}
