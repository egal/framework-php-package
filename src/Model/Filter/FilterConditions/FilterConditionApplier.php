<?php

declare(strict_types=1);

namespace Egal\Model\Filter\FilterConditions;

use Egal\Model\Builder;
use Egal\Model\Filter\FilterCondition;

abstract class FilterConditionApplier
{

    abstract public static function apply(Builder &$builder, FilterCondition $condition, string $boolean): void;

}
