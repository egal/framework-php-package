<?php

namespace Egal\Tests\Model;

use Carbon\Carbon;
use Closure;
use Egal\Model\Exceptions\UnsupportedFilterConditionException;
use Egal\Model\Exceptions\UnsupportedFieldPatternInFilterConditionException;
use Egal\Model\Exceptions\UnsupportedFilterValueTypeException;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Egal\Tests\DatabaseSchema;
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
                []
            ],
            [
                [["products.exists()", "eq", true]],
                null,
                function () {
                    return ModelActionGetItemsFilterAggregateRelationTestCategory::query()
                        ->whereHas('products')->get()->toArray();
                },
                []
            ],
            [
                [["products.exist()", "eq", true]],
                UnsupportedFieldPatternInFilterConditionException::class,
                [],
                []
            ],
            [
                [["products.exists()", "eq", 9]],
                UnsupportedFilterValueTypeException::class,
                [],
                []
            ],
            [
                [["products.exists()", "ne", true]],
                UnsupportedFilterConditionException::class,
                [],
                []
            ],
            [
                [["products.exists()", "foo", true]],
                UnsupportedFilterConditionException::class,
                [],
                []
            ]
        ];
    }

    /**
     * @dataProvider dataProviderFilterAggregateRelation
     */
    public function testFilterAggregateRelation(?array $filter, ?string $expectException, $responseExpect, array $withs)
    {
        if ($expectException) {
            $this->expectException($expectException);
        }

        $actual = ModelActionGetItemsFilterAggregateRelationTestCategory::actionGetItems(
            null,
            $withs,
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
 * @property int|bool    $id                      {@property-type field}  {@primary-key} {@validation-rules integer}
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
 * @property int    $id                           {@property-type field}  {@primary-key}
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

