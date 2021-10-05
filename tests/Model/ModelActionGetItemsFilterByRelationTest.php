<?php

namespace Egal\Tests\Model;

use Closure;
use Egal\Model\Builder;
use Egal\Model\Exceptions\RelationNotFoundException;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Egal\Tests\DatabaseSchema;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
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
        ModelTestProduct::create([
            'id' => 1,
            'name' => 'first_product',
            'category_id' => 1,
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
 * @property $category {@property-type relation}
 * @property $category_with_word {@property-type relation}
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
