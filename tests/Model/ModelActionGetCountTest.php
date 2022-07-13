<?php

namespace Egal\Tests\Model;

use Carbon\Carbon;
use Egal\Model\Filter\FilterConditions\SimpleFilterConditionApplier;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Egal\Tests\DatabaseSchema;
use Illuminate\Database\Schema\Blueprint;
use Laravel\Lumen\Application;
use PHPUnit\Framework\TestCase;

class ModelActionGetCountTest extends TestCase
{

    use DatabaseSchema;

    protected function createSchema(): void
    {
        $this->schema()->create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });

        $productsAttributes = [
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
            ['id' => 4],
        ];

        foreach ($productsAttributes as $attributes) {
            ModelActionGetCountTestProductStub::create($attributes);
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
                [],
                null,
                4,
            ],
            [
                [
                    ['id', 'eq', 5],
                ],
                null,
                0,
            ],
            [
                [
                    ['id', 'eq', 1],
                ],
                null,
                1,
            ],
            [
                [
                    ['id', 'eq', 1],
                    'OR',
                    ['id', 'eq', 2],
                ],
                null,
                2,
            ],
        ];
    }

    /**
     * @dataProvider productsFilterDataProvider
     */
    public function testProductsFilter(?array $filter, ?string $expectException, int $equalsExpect)
    {
        if ($expectException) {
            $this->expectException($expectException);
        }

        $actual = ModelActionGetCountTestProductStub::actionGetCount($filter)['count'];

        if ($equalsExpect) {
            $this->assertEquals($equalsExpect, $actual);
        }
    }

}

/**
 * @property int    $id                           {@property-type field}  {@primary-key}
 * @property Carbon $created_at                   {@property-type field}  {@validation-rules date}
 * @property Carbon $updated_at                   {@property-type field}  {@validation-rules date}
 */
class ModelActionGetCountTestProductStub extends Model
{

    protected $table = 'products';
    protected $guarded = [];
    protected $fillable = [];

    public function getModelMetadata(): ModelMetadata
    {
        return new ModelMetadata(static::class);
    }

}
