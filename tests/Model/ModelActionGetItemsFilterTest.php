<?php

namespace Egal\Tests\Model;

use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Egal\Tests\DatabaseSchema;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;

class ModelActionGetItemsFilterTest extends TestCase
{

    use DatabaseSchema;

    protected function createSchema(): void
    {
        $this->schema()->create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        $productsAttributes = [
            ['id' => 1, 'name' => 'first_product'],
            ['id' => 2, 'name' => 'second_product'],
            ['id' => 3, 'name' => 'product_third'],
            ['id' => 4, 'name' => 'product_fourth'],
        ];

        foreach ($productsAttributes as $attributes) {
            ModelActionGetItemsFilterTestProductStub::create($attributes);
        }
    }

    protected function dropSchema(): void
    {
        $this->schema()->drop('products');
    }

    public function productsFilterDataProvider()
    {
        return [
            [
                [
                    ['name', 'eq', 'first_product'],
                ],
                null,
                [1]
            ],
            [
                [
                    ['name', 'eq', 'first_product'],
                    'OR',
                    ['name', 'eq', 'second_product'],
                ],
                null,
                [1, 2]
            ],
            [
                [
                    ['name', 'ne', 'first_product'],
                ],
                null,
                [2, 3, 4]
            ],
            [
                [
                    ['name', 'ne', 'first_product'],
                    'AND',
                    ['name', 'ne', 'second_product'],
                ],
                null,
                [3, 4]
            ],
            [
                [
                    ['name', 'ne', 'first_product'],
                    'OR',
                    ['name', 'ne', 'second_product'],
                ],
                null,
                [1, 2, 3, 4]
            ],
            [
                [
                    ['name', 'co', 'product'],
                ],
                null,
                [1, 2, 3, 4]
            ],
            [
                [
                    ['name', 'nc', 'product'],
                ],
                null,
                []
            ],
            [
                [
                    ['name', 'sw', 'product'],
                ],
                null,
                [3, 4]
            ],
            [
                [
                    ['name', 'ew', 'product'],
                ],
                null,
                [1, 2]
            ],
        ];
    }

    /**
     * @dataProvider productsFilterDataProvider()
     */
    public function testProductsFilter(?array $filter, ?string $expectException, array $equalsExpect)
    {
        if ($expectException) {
            $this->expectException($expectException);
        }

        $actual = array_column(ModelActionGetItemsFilterTestProductStub::actionGetItems(
            null,
            [],
            $filter,
            []
        )['items'], 'id');

        if ($equalsExpect) {
            $this->assertEquals([], array_diff($equalsExpect, $actual));
        }
    }

}

class ModelActionGetItemsFilterTestProductStub extends Model
{

    protected $table = 'products';
    protected $guarded = [];
    protected $fillable = [];

    public function getModelMetadata(): ModelMetadata
    {
        return new ModelMetadata(static::class);
    }

}
