<?php

namespace EgalFramework\Metadata\Tests;

use EgalFramework\Metadata\RelationDirection;
use PHPUnit\Framework\TestCase;

class RelationDirectionTest extends TestCase
{

    public function testRelationDirection()
    {
        $relationDirection = new RelationDirection('Model');
        $this->assertEquals(['model' => 'Model', 'id' => 0], $relationDirection->toArray());
        $relationDirection = new RelationDirection('Model', 10);
        $this->assertEquals(['model' => 'Model', 'id' => 10], $relationDirection->toArray());
        $relationDirection->setId(20);
        $this->assertEquals(['model' => 'Model', 'id' => 20], $relationDirection->toArray());
    }

}
