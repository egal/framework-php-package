<?php

namespace Egal\Tests\Model;

use Carbon\Carbon;
use Closure;
use Egal\Model\Exceptions\FieldNotFoundException;
use Egal\Model\Exceptions\UnsupportedAggregateFunctionException;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Egal\Tests\DatabaseSchema;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;

class ModelActionGetItemsFilterAggregateRelationTest extends TestCase
{

    use DatabaseSchema;

    protected function createSchema(): void
    {
        $this->schema()->create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });

        $this->schema()->create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('category_id');
            $table->timestamps();
        });

        ModelActionGetItemsFilterAggregateRelationTestCategory::create(['id' => 1]);
        ModelActionGetItemsFilterAggregateRelationTestCategory::create(['id' => 2]);
        ModelActionGetItemsFilterAggregateRelationTestProduct::create(['id' => 1, 'category_id' => 1]);
        ModelActionGetItemsFilterAggregateRelationTestProduct::create(['id' => 2, 'category_id' => 1]);
        ModelActionGetItemsFilterAggregateRelationTestProduct::create(['id' => 3, 'category_id' => 1]);
    }

    protected function dropSchema(): void
    {
        $this->schema()->drop('products');
        $this->schema()->drop('categories');
    }

    public function dataProviderFilterAggregateRelation(): array
    {
        return [
            [
                [["products.exists()", "eq", false]],
                null,
                function () {
                    return ModelActionGetItemsFilterAggregateRelationTestCategory::query()
                        ->whereDoesntHave('products')->get()->toArray();
                },
            ],
            [
                [["products.count()", "eq", 3]],
                null,
                function () {
                    return ModelActionGetItemsFilterAggregateRelationTestCategory::query()
                        ->whereHas('products', null, '=', 3)->get()->toArray();
                },
            ],
            [
                [["products.count()", "eq", 2]],
                null,
                function () {
                    return [];
                },
            ],
            [
                [["test.count()", "eq", 2]],
                RelationNotFoundException::class,
               null,
            ],
            [
                [["products.avg(id)", "eq", 2]],
                null,
                function () {
                    return ModelActionGetItemsFilterAggregateRelationTestCategory::query()
                        ->withAggregate('products', 'id', 'avg')
                        ->get()
                        ->where('products_avg_id', '=', 2)
                        ->makeHidden('products_avg_id')
                        ->toArray();
                },
            ],
            [
                [["products.avg(test)", "eq", 2]],
                FieldNotFoundException::class,
                null,
            ],
            [
                [["products.max(id)", "eq", 2]],
                null,
                function () {
                    return [];
                },
            ],
            [
                [["products.test(id)", "eq", 2]],
                UnsupportedAggregateFunctionException::class,
                null,
            ]
        ];
    }

    /**
     * @dataProvider dataProviderFilterAggregateRelation
     */
    public function testFilterAggregateRelation(?array $filter, ?string $expectException, $responseExpect)
    {
        if ($expectException) {
            $this->expectException($expectException);
        }

        $actual = ModelActionGetItemsFilterAggregateRelationTestCategory::actionGetItems(
            null,
            [],
            $filter,
            []
        )['items'];

        if ($responseExpect instanceof Closure) {
            $responseExpect = $responseExpect();
        }

        $this->assertEquals(
            $responseExpect,
            $actual
        );
    }

}

/**
 * @property int|bool    $id                      {@property-type field}  {@prymary-key} {@validation-rules integer}
 * @property Carbon $created_at                   {@property-type field}  {@validation-rules date}
 * @property Carbon $updated_at                   {@property-type field}  {@validation-rules date}
 *
 * @property $products {@property-type relation}
 */
class ModelActionGetItemsFilterAggregateRelationTestCategory extends Model
{

    protected $table = 'categories';
    protected $guarded = [];

    public function getModelMetadata(): ModelMetadata
    {
        return new ModelMetadata(static::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(ModelActionGetItemsFilterAggregateRelationTestProduct::class, 'category_id', 'id');
    }

}

/**
 * @property int    $id                           {@property-type field}  {@prymary-key}
 * @property int    $category_id                  {@property-type field}  {@validation-rules int}
 * @property Carbon $created_at                   {@property-type field}  {@validation-rules date}
 * @property Carbon $updated_at                   {@property-type field}  {@validation-rules date}
 *
 * @property $category {@property-type relation}
 *
 * @action create         {@statuses-access guest}
 * @action getItems       {@statuses-access guest}
 */
class ModelActionGetItemsFilterAggregateRelationTestProduct extends Model
{

    protected $table = 'products';
    protected $guarded = [];
    protected $fillable = [];

    public function getModelMetadata(): ModelMetadata
    {
        return new ModelMetadata(static::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ModelActionGetItemsFilterAggregateRelationTestCategory::class);
    }

}

