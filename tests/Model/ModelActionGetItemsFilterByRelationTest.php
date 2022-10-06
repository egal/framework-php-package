<?php

namespace Egal\Tests\Model;

use Carbon\Carbon;
use Closure;
use Egal\Model\Builder;
use Egal\Model\Exceptions\RelationNotFoundException;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Egal\Tests\DatabaseSchema;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Laravel\Lumen\Application;
use PHPUnit\Framework\TestCase;

class ModelActionGetItemsFilterByRelationTest extends TestCase
{

    use DatabaseSchema;

    /**
     * Setup the database schema.
     *
     * @return void
     */
    protected function createSchema(): void
    {
        $this->schema()->create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('sale')->nullable();
            $table->timestamps();
        });

        $this->schema()->create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->foreignId('category_id');
            $table->timestamps();
        });
    }

    protected function dropSchema(): void
    {
        $this->schema()->drop('products');
        $this->schema()->drop('categories');
    }

    protected function seedData(): void
    {
        ModelTestCategory::create([
            'id' => 1,
            'name' => 'first_category',
        ]);
        ModelTestCategory::create([
            'id' => 2,
            'name' => 'first_category',
            'sale' => 30
        ]);
        ModelTestProduct::create([
            'id' => 1,
            'name' => 'first_product',
            'category_id' => 1,
        ]);
        ModelTestProduct::create([
            'id' => 2,
            'name' => 'second_product',
            'category_id' => 2,
        ]);
    }

    public function productsFilterDataProvider()
    {
        return [
            [
                [['foo.bar', 'eq', 1]],
                RelationNotFoundException::class,
                null
            ],
            [
                [['category.id', 'eq', 2]],
                null,
                []
            ],
            [
                [['category.id', 'eq', 1]],
                null,
                function () {
                    return ModelTestProduct::query()->whereHas('category', function (Builder $query) {
                        $query->where('id', '=', 1);
                    })->get()->toArray();
                },
            ],
            [
                [['category_with_word.id', 'eq', 1000]],
                null,
                [],
            ],
            [
                [
                    ['category.id', 'eq', 1000],
                    'AND',
                    ['category_with_word.id', 'eq', 1000],
                ],
                null,
                [],
            ],
            [
                [
                    ['category.id', 'eq', 1],
                    'OR',
                    ['category_with_word.id', 'eq', 1000],
                ],
                null,
                function () {
                    return ModelTestProduct::query()->whereHas('category', function (Builder $query) {
                        $query->where('id', '=', 1);
                    })->get()->toArray();
                },
            ],
            [
                [['category.sale', 'eq', null]],
                null,
                function () {
                    return ModelTestProduct::query()->whereHas('category', function (Builder $query) {
                        $query->where('sale', '=', null);
                    })->get()->toArray();
                },
            ],
            [
                [['category_with_word.created_at', 'le', Carbon::now()->toDateTimeString()],],
                null,
                function () {
                    return ModelTestProduct::query()->whereHas('category', function (Builder $query) {
                        $query->where('created_at', '<=', Carbon::now()->toDateTimeString());
                    })->get()->toArray();
                },
            ],
        ];
    }

    /**
     * @dataProvider productsFilterDataProvider()
     * @param array|\Closure $equalsExpect
     */
    public function testProductsFilter(?array $filter, ?string $expectException, $equalsExpect)
    {
        $this->seedData();

        if ($expectException) {
            $this->expectException($expectException);
        }

        $actual = ModelTestProduct::actionGetItems(
            null,
            [],
            $filter,
            []
        )['items'];

        if ($equalsExpect) {
            if ($equalsExpect instanceof Closure) {
                $equalsExpect = $equalsExpect();
            }

            $this->assertEquals(
                $equalsExpect,
                $actual
            );
        }
    }

}

/**
 * @property int|bool    $id                      {@property-type field}  {@primary-key} {@validation-rules integer}
 * @property string $name       Название          {@property-type field}  {@validation-rules string}
 * @property string $sale       Скидка          {@property-type field}  {@validation-rules int}
 * @property Carbon $created_at                   {@property-type field}  {@validation-rules date}
 * @property Carbon $updated_at                   {@property-type field}  {@validation-rules date}
 */
class ModelTestCategory extends Model
{

    protected $table = 'categories';
    protected $guarded = [];

    public function getModelMetadata(): ModelMetadata
    {
        return new ModelMetadata(static::class);
    }

}

/**
 * @property int    $id                            {@property-type field}  {@primary-key}
 * @property string $name        Название          {@property-type field}  {@validation-rules string}
 * @property int    $category_id Категория         {@property-type field}  {@validation-rules int}
 * @property Carbon $created_at                    {@property-type field}  {@validation-rules date}
 * @property Carbon $updated_at                    {@property-type field}  {@validation-rules date}
 *
 * @property ModelTestCategory $category           {@property-type relation}
 * @property ModelTestCategory $category_with_word {@property-type relation}
 */
class ModelTestProduct extends Model
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
        return $this->belongsTo(ModelTestCategory::class);
    }

    public function categoryWithWord(): BelongsTo
    {
        return $this->belongsTo(ModelTestCategory::class, 'category_id', 'id');
    }

}
