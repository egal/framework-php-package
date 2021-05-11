<?php

namespace EgalFramework\FilterQuery\Tests;

use EgalFramework\FilterQuery\FilterQuery;
use EgalFramework\FilterQuery\Exception;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{

    /**
     * @throws Exception
     */
    public function testFilter()
    {
        $uri = [
            '_search' => json_encode(['name' => 'vary']),
            '_order_by' => 'id',
            '_order' => 'asc',
            '_range_from' => json_encode(['price' => 123]),
            '_range_to' => json_encode(['price' => 222]),
            'email' => 'nobody@example.com',
            'id' => '[4,6]',
            '_from' => 2,
            '_count' => 105,
            '_full_search' => 'log',
            '_rel_id' => 213,
            '_rel_model' => 'RelationModel',
        ];
        $filter = new FilterQuery();
        $filter->setQuery($uri);
        $this->assertEquals(['name' => 'vary'], $filter->getSubstringSearch());
        $this->assertEquals(['price' => 123], $filter->getFrom());
        $this->assertEquals(['price' => 222], $filter->getTo());
        $this->assertEquals('id', $filter->getOrderBy());
        $this->assertEquals('asc', $filter->getOrder());
        $this->assertEquals(['email' => 'nobody@example.com', 'id' => [4, 6]], $filter->getFields());
        $this->assertEquals(2, $filter->getLimitFrom());
        $this->assertEquals(100, $filter->getLimitCount());
        $this->assertEquals('log', $filter->getFullSearch());
        $this->assertEquals([213], $filter->getRelationId());
        $this->assertEquals(100, $filter->getMaxCount());
        $this->assertEquals('', $filter->getField('asd'));
        $this->assertEquals('RelationModel', $filter->getRelationModel());
        $this->assertEquals(100, $filter->getMaxCount());
        $this->assertEquals('', $filter->getField('asd'));
        $this->assertEquals('RelationModel', $filter->getRelationModel());
        $this->assertEquals([], $filter->getWith());
        $uri['_with'] = json_encode([132]);
        $filter->setQuery($uri);
        $this->assertEquals([132], $filter->getWith());
    }

    /**
     * @throws Exception
     */
    public function testRelationId()
    {
        $uri = [
            '_rel_id' => 123,
        ];
        $filter = new FilterQuery();
        $filter->setQuery($uri);
        $this->assertEquals([123], $filter->getRelationId());
        $filter = new FilterQuery();
        $uri = [
            '_rel_id' => null
        ];
        $filter->setQuery($uri);
        $this->assertEquals([], $filter->getRelationId());

        $filter = new FilterQuery();
        $uri = [
            '_rel_id' => '{"a":1,"b":2}'
        ];
        $filter->setQuery($uri);
        $this->assertEquals(['a' => 1, 'b' => 2], $filter->getRelationId());

        $filter = new FilterQuery();
        $uri = [
            '_rel_id' => '{"qwe'
        ];
        $this->expectException('Exception');
        $filter->setQuery($uri);
    }

    /**
     * @throws Exception
     */
    public function testFieldSearch()
    {
        $filter = new FilterQuery();
        $filter->setQuery(['test' => '123', 'test2' => '']);
        $this->assertEquals('', $filter->getFullSearch());
        $this->assertEquals('123', $filter->getField('test'));
    }

    /**
     * @throws Exception
     */
    public function testJSONError()
    {
        $uri = [
            '_search' => '{"qwe',
        ];
        $filter = new FilterQuery();
        $this->expectException('Exception');
        $filter->setQuery($uri);
    }

    /**
     * @throws Exception
     */
    public function testLimits()
    {
        $uri = [
            '_from' => -10,
            '_count' => 1000,
        ];
        $filter = new FilterQuery();
        $filter->setQuery($uri);
        $this->assertEquals(0, $filter->getLimitFrom());
        $this->assertEquals(FilterQuery::SEARCH_COUNT_MAX, $filter->getLimitCount());
        $uri = [
            '_count' => -1
        ];
        $filter = new FilterQuery();
        $filter->setQuery($uri);
        $this->assertEquals(1, $filter->getLimitCount());
    }

}
