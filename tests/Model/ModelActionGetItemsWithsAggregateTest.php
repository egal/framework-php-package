<?php

namespace Egal\Tests\Model;

use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Egal\Tests\DatabaseSchema;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;

class ModelActionGetItemsWithsAggregateTest extends TestCase
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

        ModelActionGetItemsWithsAggregateTestCategory::create(['id' => 1]);
        ModelActionGetItemsWithsAggregateTestProduct::create(['id' => 1, 'category_id' => 1]);
        ModelActionGetItemsWithsAggregateTestProduct::create(['id' => 2, 'category_id' => 1]);
        ModelActionGetItemsWithsAggregateTestProduct::create(['id' => 3, 'category_id' => 1]);
    }

    protected function dropSchema(): void
    {
        $this->schema()->drop('products');
        $this->schema()->drop('categories');
    }

    public function dataProviderWiths()
    {
        return [
            [
                ['products.count()' => []],
                null,
                'products_count',
                3,
            ],
            [
                ['products.count()'],
                null,
                'products_count',
                3,
            ],
            [
                ['products.sum(id)'],
                null,
                'products_sum_id',
                6,
            ],
            [
                ['products.max(id)'],
                null,
                'products_max_id',
                3,
            ],
            [
                ['products.min(id)'],
                null,
                'products_min_id',
                1,
            ],
            [
                ['products.avg(id)'],
                null,
                'products_avg_id',
                2,
            ],
            [
                ['products.exists()'],
                null,
                'products_exists',
                true,
            ],
            [
                ['dd.avg()'],
                RelationNotFoundException::class,
                null,
                null,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderWiths
     */
    public function testWiths(?array $withs, ?string $expectException, $equalsKeyExpect, $equalsValueExpect)
    {
        if ($expectException) {
            $this->expectException($expectException);
        }

        $actual = ModelActionGetItemsWithsAggregateTestCategory::actionGetItems(
            null,
            $withs,
            [['id', 'eq', 1]],
            []
        )['items'][0];

        if ($equalsKeyExpect && $equalsValueExpect) {
            $this->assertEquals($equalsValueExpect, $actual[$equalsKeyExpect]);
        }
    }

}

/**
 * @property $products {@property-type relation}
 */
class ModelActionGetItemsWithsAggregateTestCategory extends Model
{

    protected $table = 'categories';
    protected $guarded = [];

    public function getModelMetadata(): ModelMetadata
    {
        return new ModelMetadata(static::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(ModelActionGetItemsWithsAggregateTestProduct::class, 'category_id', 'id');
    }

}

class ModelActionGetItemsWithsAggregateTestProduct extends Model
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
        return $this->belongsTo(ModelActionGetItemsWithsAggregateTestCategory::class);
    }

}

