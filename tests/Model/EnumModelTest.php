<?php

namespace Egal\Tests\Model;

use Egal\Model\EnumModel;
use PHPUnit\Framework\TestCase;

class EnumModelTest extends TestCase
{
    public function enumGetItemsDataProvider()
    {
        return [
            [
                [],
                [
                    [
                        "key" => "INCOMPLETE",
                        "value" => "incomplete",
                        "description" => "test description"
                    ],
                    [
                        "key" => "COMPLETE",
                        "value" => "complete",
                        "description" => "test description"
                    ]
                ],
            ],
            [
                [
                    ['key', 'eq', "INCOMPLETE"],
                ],
                [
                    [
                        "key" => "INCOMPLETE",
                        "value" => "incomplete",
                        "description" => "test description"
                    ]
                ],
            ],
            [
                [
                    ['key', 'eq', "NON EXIST"],
                ],
                [],
            ],
            [
                [
                    ['value', 'eq', "incomplete"],
                    'OR',
                    ['value', 'eq', "complete"],
                ],
                [
                    [
                        "key" => "INCOMPLETE",
                        "value" => "incomplete",
                        "description" => "test description"
                    ],
                    [
                        "key" => "COMPLETE",
                        "value" => "complete",
                        "description" => "test description"
                    ]
                ],
            ],
        ];
    }

    public function enumGetItemDataProvider()
    {
        return [
            [
                "INCOMPLETE",
                [
                    "key" => "INCOMPLETE",
                    "value" => "incomplete",
                    "description" => "test description"
                ],
                null
            ],
            [
                "COMPLETE",
                [
                    "key" => "COMPLETE",
                    "value" => "complete",
                    "description" => "test description"
                ],
                null
            ]
        ];
    }

    /**
     * @dataProvider enumGetItemsDataProvider
     */
    public function testEnumGetItems(array $filter, array $expectResult)
    {
        $actualResult = EnumModelActionGetItemsTestStatusStub::actionGetItems(null, $filter);

        $this->assertEquals($expectResult, $actualResult['items']);
    }

    /**
     * @dataProvider enumGetItemDataProvider
     */
    public function testEnumGetItem($keyValue, array $expectResult, ?string $expectException)
    {
        if ($expectException !== null) {
            $this->expectException($expectException);
        }

        $actualResult = EnumModelActionGetItemsTestStatusStub::actionGetItem($keyValue);

        $this->assertEquals($expectResult, $actualResult);

    }

    public function testEnumGetCount()
    {
        $actualResult = EnumModelActionGetItemsTestStatusStub::actionGetCount();


        $this->assertEquals(['count' => 2], $actualResult);
    }

}

class EnumModelActionGetItemsTestStatusStub extends EnumModel
{
    const INCOMPLETE = 'incomplete';
    const COMPLETE = 'complete';

    public static function descriptions(): array
    {
        return [
            self::INCOMPLETE => 'test description',
            self::COMPLETE => 'test description'
        ];
    }
}
