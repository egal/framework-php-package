<?php

namespace EgalFramework\Metadata\Tests;

use EgalFramework\Common\RelationType;
use EgalFramework\Metadata\Relation;
use PHPUnit\Framework\TestCase;

class RelationTest extends TestCase
{

    public function testSimpleRelation()
    {
        $relation = new Relation(RelationType::MANY_TO_MANY, 'User');
        $this->assertEquals(RelationType::MANY_TO_MANY, $relation->getType());
        $this->assertEquals('User', $relation->getRelationModel());
        $this->assertEmpty($relation->getIntermediateModel());
        $this->assertEquals(
            [
                'type' => RelationType::MANY_TO_MANY,
                'relationModel' => 'User',
            ],
            $relation->toArray()
        );
        $this->assertEquals('', $relation->getIntermediateTable());
    }

    public function testManyRelation()
    {
        $relation = new Relation(RelationType::ONE_TO_ONE, 'Team', 'UserRole');
        $this->assertEquals(RelationType::ONE_TO_ONE, $relation->getType());
        $this->assertEquals('Team', $relation->getRelationModel());
        $this->assertEquals('UserRole', $relation->getIntermediateModel());
        $this->assertEquals(
            [
                'type' => RelationType::ONE_TO_ONE,
                'relationModel' => 'Team',
                'intermediateModel' => 'UserRole',
            ],
            $relation->toArray()
        );
        $this->assertEquals('user_roles', $relation->getIntermediateTable());
    }

}
