<?php

namespace Egal\Tests\Model;

use Carbon\Carbon;
use Egal\Model\Filter\FilterConditions\SimpleFilterConditionApplier;
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
            $table->integer('count');
            $table->integer('sale')->nullable();
            $table->timestamps();
        });

        $productsAttributes = [
            ['id' => 1, 'name' => 'first_product', 'count' => 1],
            ['id' => 2, 'name' => 'second_product', 'count' => 2, 'sale' => 30],
            ['id' => 3, 'name' => 'product_third', 'count' => 3],
            ['id' => 4, 'name' => 'product_fourth', 'count' => 4, 'sale'  => 50],
        ];

        foreach ($productsAttributes as $attributes) {
            ModelActionGetItemsFilterTestProductStub::create($attributes);
        }
    }

    protected function dropSchema(): void
    {
        $this->schema()->drop('products');
    }

    public function productsFilterDataProviderEq()
    {
        return [
            [
                [
                    ['name', 'eq', 'first_product'],
                ],
                null,
                [1],
            ],
            [
                [
                    ['name', 'eq', 'first_product'],
                    'OR',
                    ['name', 'eq', 'second_product'],
                ],
                null,
                [1, 2],
            ],
            [
                [
                    ['name', 'eq', 'first_product'],
                    'AND',
                    [['name', 'eq', 'first_product']]
                ],
                null,
                [1],
            ],
            [
                [['sale', 'eq', null]],
                null,
                [1, 3],
            ],
            [
                [
                    ['created_at', 'eq', Carbon::now()->addDay()->toDateTimeString()]
                ],
                null,
                [],
            ],
        ];
    }

    public function productsFilterDataProviderEqi()
    {
        return [
            [
                [
                    ['name', 'eqi', 'fIrSt_PrOdUcT'],
                ],
                null,
                [1],
            ],
            [
                [
                    ['created_at', 'eqi', Carbon::now()->addDay()->toDateTimeString()]
                ],
                null,
                [],
            ],
        ];
    }

    public function productsFilterDataProviderNe()
    {
        return [
            [
                [
                    ['name', 'ne', 'first_product'],
                ],
                null,
                [2, 3, 4],
            ],
            [
                [
                    ['name', 'ne', 'first_product'],
                    'AND',
                    ['name', 'ne', 'second_product'],
                ],
                null,
                [3, 4],
            ],
            [
                [
                    ['name', 'ne', 'first_product'],
                    'OR',
                    ['name', 'ne', 'second_product'],
                ],
                null,
                [1, 2, 3, 4],
            ],
            [
                [
                    ['created_at', 'ne', Carbon::now()->addDay()->toDateTimeString()]
                ],
                null,
                [1, 2, 3, 4],
            ],
        ];
    }

    public function productsFilterDataProviderNei()
    {
        return [
            [
                [
                    ['name', 'nei', 'fIrSt_PrOdUcT'],
                ],
                null,
                [2, 3, 4],
            ],
        ];
    }

    public function productsFilterDataProviderCo()
    {
        return [
            [
                [
                    ['name', 'co', 'product'],
                ],
                null,
                [1, 2, 3, 4],
            ],
        ];
    }

    public function productsFilterDataProviderCoi()
    {
        return [
            [
                [
                    ['name', 'coi', 'pRoDuCt'],
                ],
                null,
                [1, 2, 3, 4],
            ],
        ];
    }

    public function productsFilterDataProviderNc()
    {
        return [
            [
                [
                    ['name', 'nc', 'product'],
                ],
                null,
                [],
            ],
        ];
    }

    public function productsFilterDataProviderNci()
    {
        return [
            [
                [
                    ['name', 'nci', 'pRoDuCt'],
                ],
                null,
                [],
            ],
        ];
    }

    public function productsFilterDataProviderSw()
    {
        return [
            [
                [
                    ['name', 'sw', 'product'],
                ],
                null,
                [3, 4],
            ],
        ];
    }

    public function productsFilterDataProviderSwi()
    {
        return [
            [
                [
                    ['name', 'swi', 'pRoDuCt'],
                ],
                null,
                [3, 4],
            ],
        ];
    }

    public function productsFilterDataProviderEw()
    {
        return [
            [
                [
                    ['name', 'ew', 'product'],
                ],
                null,
                [1, 2],
            ],
        ];
    }

    public function productsFilterDataProviderEwi()
    {
        return [
            [
                [
                    ['name', 'ewi', 'pRoDuCt'],
                ],
                null,
                [1, 2],
            ],
        ];
    }

    public function productsFilterDataProviderGt()
    {
        return [
            [
                [
                    ['count', 'gt', 1],
                ],
                null,
                [2, 3, 4],
            ],
            [
                [
                    ['created_at', 'gt', Carbon::now()->toDateTimeString()]
                ],
                null,
                [],
            ],
        ];
    }

    public function productsFilterDataProviderGe()
    {
        return [
            [
                [
                    ['count', 'ge', 1],
                ],
                null,
                [1, 2, 3, 4],
            ],
        ];
    }

    public function productsFilterDataProviderLt()
    {
        return [
            [
                [
                    ['count', 'lt', 2],
                ],
                null,
                [1],
            ],
        ];
    }

    public function productsFilterDataProviderLe()
    {
        return [
            [
                [
                    ['count', 'le', 2],
                ],
                null,
                [1, 2],
            ],
        ];
    }

    /**
     * @dataProvider productsFilterDataProviderEq   {@see SimpleFilterConditionApplier::EQUAL_OPERATOR}
     * @dataProvider productsFilterDataProviderEqi  {@see SimpleFilterConditionApplier::EQUAL_IGNORE_CASE_OPERATOR}
     * @dataProvider productsFilterDataProviderNe   {@see SimpleFilterConditionApplier::NOT_EQUAL_OPERATOR}
     * @dataProvider productsFilterDataProviderNei  {@see SimpleFilterConditionApplier::NOT_EQUAL_IGNORE_CASE_OPERATOR}
     * @dataProvider productsFilterDataProviderGt   {@see SimpleFilterConditionApplier::GREATER_THEN_OPERATOR}
     * @dataProvider productsFilterDataProviderGe   {@see SimpleFilterConditionApplier::GREATER_OR_EQUAL_OPERATOR}
     * @dataProvider productsFilterDataProviderLt   {@see SimpleFilterConditionApplier::LESS_THEN_OPERATOR}
     * @dataProvider productsFilterDataProviderLe   {@see SimpleFilterConditionApplier::LESS_OR_EQUAL_OPERATOR}
     * @dataProvider productsFilterDataProviderCo   {@see SimpleFilterConditionApplier::CONTAIN_OPERATOR}
     * @dataProvider productsFilterDataProviderCoi  {@see SimpleFilterConditionApplier::CONTAIN_IGNORE_CASE_OPERATOR}
     * @dataProvider productsFilterDataProviderNc   {@see SimpleFilterConditionApplier::NOT_CONTAIN_OPERATOR}
     * @dataProvider productsFilterDataProviderNci  {@see SimpleFilterConditionApplier::NOT_CONTAIN_IGNORE_CASE_OPERATOR}
     * @dataProvider productsFilterDataProviderSw   {@see SimpleFilterConditionApplier::START_WITH_OPERATOR}
     * @dataProvider productsFilterDataProviderSwi  {@see SimpleFilterConditionApplier::START_WITH_IGNORE_CASE_OPERATOR}
     * @dataProvider productsFilterDataProviderEw   {@see SimpleFilterConditionApplier::END_WITH_OPERATOR}
     * @dataProvider productsFilterDataProviderEwi  {@see SimpleFilterConditionApplier::END_WITH_IGNORE_CASE_OPERATOR}
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
            $this->assertEquals(
                [],
                array_diff($equalsExpect, $actual),
                'Expect: ' . implode(', ', $equalsExpect) . '.' . 'Actual: ' . implode(', ', $actual) . '.'
            );
        }
    }

}

/**
 * @property int    $id                           {@property-type field}  {@primary-key}
 * @property string $name       Название          {@property-type field}  {@validation-rules string}
 * @property string $count      Количество        {@property-type field}  {@validation-rules int}
 * @property string $sale       Скидка            {@property-type field}  {@validation-rules int}
 * @property Carbon $created_at                   {@property-type field}  {@validation-rules date}
 * @property Carbon $updated_at                   {@property-type field}  {@validation-rules date}
 *
 * @action create         {@statuses-access guest}
 * @action getItems       {@statuses-access guest}
 */
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
