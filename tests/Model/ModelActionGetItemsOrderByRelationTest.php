<?php

namespace Egal\Tests\Model;

use Egal\Model\Builder;
use Egal\Model\Filter\FilterConditions\SimpleFilterConditionApplier;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Egal\Tests\DatabaseSchema;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;

class ModelActionGetItemsOrderByRelationTest extends TestCase
{

    use DatabaseSchema;

    protected function createSchema(): void
    {
        $this->schema()->create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        $this->schema()->create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('category_id');
            $table->timestamps();
        });

        ModelActionGetItemsOrderByRelationTestCategory::create(['id' => 1, 'name' => 'Aa']);
        ModelActionGetItemsOrderByRelationTestCategory::create(['id' => 2, 'name' => 'Bb']);
        ModelActionGetItemsOrderByRelationTestCategory::create(['id' => 3, 'name' => 'Cc']);
        ModelActionGetItemsOrderByRelationTestProduct::create(['id' => 1, 'category_id' => 3]);
        ModelActionGetItemsOrderByRelationTestProduct::create(['id' => 2, 'category_id' => 2]);
        ModelActionGetItemsOrderByRelationTestProduct::create(['id' => 3, 'category_id' => 1]);
    }

    protected function dropSchema(): void
    {
        $this->schema()->drop('products');
        $this->schema()->drop('categories');
    }

    public function dataProviderOrder()
    {
        return [
            [
                [],
                null,
                [1, 2, 3]
            ],
            [
                ['category_id', 'desc'],
                null,
                [1, 2, 3]
            ],
            [
                ['category.name', 'desc'],
                null,
                [1, 2, 3]
            ],
        ];
    }

    /**
     * @dataProvider dataProviderOrder
     */
    public function testOrder(?array $order, ?string $expectException, array $equalsExpect)
    {
        if ($expectException) {
            $this->expectException($expectException);
        }

        $actual = array_column(ModelActionGetItemsOrderByRelationTestProduct::actionGetItems(
            null,
            [],
            [],
            $order
        )['items'], 'id');

        if ($equalsExpect) {
            $this->assertEquals($equalsExpect, $actual);
        }
    }

}

class ModelActionGetItemsOrderByRelationTestCategory extends Model
{

    protected $table = 'categories';
    protected $guarded = [];

    public function getModelMetadata(): ModelMetadata
    {
        return new ModelMetadata(static::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(ModelActionGetItemsOrderByRelationTestProduct::class, 'category_id', 'id');
    }

}

class ModelActionGetItemsOrderByRelationTestProduct extends Model
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
        return $this->belongsTo(ModelActionGetItemsOrderByRelationTestCategory::class);
    }

    public function orderByCategory(Builder &$builder, string $column, string $direction)
    {
        $builder->join('categories', 'products.category_id', '=', 'categories.id', 'left', false);
        $builder->addSelect('products.*');
        $builder->addSelect('categories.' . $column . ' as category_' . $column);
        $builder->orderBy('category_' . $column, $direction);
    }

}
