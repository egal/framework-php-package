<?php

namespace Egal\Tests\Model;

use Closure;
use Egal\Model\Builder;
use Egal\Model\Exceptions\RelationNotFoundException;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Egal\Tests\DatabaseSchema;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;

class ModelActionGetItemsFilterByMorphRelationTest extends TestCase
{

    use DatabaseSchema;

    /**
     * Setup the database schema.
     *
     * @return void
     */
    protected function createSchema(): void
    {
        $this->schema()->create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
        ModelActionGetItemsFilterByMorphRelationTestProduct::create([
            'id' => 1,
            'name' => 'first',
        ]);

        $this->schema()->create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });
        ModelActionGetItemsFilterByMorphRelationTestOrder::create(['id' => 1]);

        $this->schema()->create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('commentable');
            $table->timestamps();
        });
        ModelActionGetItemsFilterByMorphRelationTestComment::create([
            'id' => 1,
            'commentable_type' => ModelActionGetItemsFilterByMorphRelationTestProduct::class,
            'commentable_id' => 1,
        ]);
        ModelActionGetItemsFilterByMorphRelationTestComment::create([
            'id' => 2,
            'commentable_type' => ModelActionGetItemsFilterByMorphRelationTestOrder::class,
            'commentable_id' => 1,
        ]);
    }

    protected function dropSchema(): void
    {
        $this->schema()->drop('comments');
        $this->schema()->drop('products');
        $this->schema()->drop('orders');
    }

    public function dataProviderFilter()
    {
        return [
            [
                [],
                null,
                [1, 2]
            ],
            [
                [
                    ['commentable.id', 'eq', 1],
                ],
                null,
                [1, 2]
            ],
            [
                [
                    ['commentable[' . ModelActionGetItemsFilterByMorphRelationTestProduct::class . '].name', 'eq', 'first'],
                ],
                null,
                [1]
            ],
        ];
    }

    /**
     * @dataProvider dataProviderFilter
     * @group current
     */
    public function testFilter(?array $filter, ?string $expectException, ?array $equalsExpect)
    {
        if ($expectException) {
            $this->expectException($expectException);
        }

        $actual = array_column(ModelActionGetItemsFilterByMorphRelationTestComment::actionGetItems(
            null,
            [],
            $filter,
            []
        )['items'], 'id');

        if ($equalsExpect) {
            $this->assertEquals($equalsExpect, $actual);
        }
    }

}

class ModelActionGetItemsFilterByMorphRelationTestProduct extends Model
{

    protected $table = 'products';
    protected $guarded = [];
    protected $fillable = [];

    public function getModelMetadata(): ModelMetadata
    {
        return new ModelMetadata(static::class);
    }

    public function comment(): MorphOne
    {
        return $this->morphOne(ModelActionGetItemsFilterByMorphRelationTestComment::class, 'to');
    }

}

class ModelActionGetItemsFilterByMorphRelationTestOrder extends Model
{

    protected $table = 'orders';
    protected $guarded = [];
    protected $fillable = [];

    public function getModelMetadata(): ModelMetadata
    {
        return new ModelMetadata(static::class);
    }

    public function comment(): MorphOne
    {
        return $this->morphOne(ModelActionGetItemsFilterByMorphRelationTestComment::class, 'to');
    }

}

/**
 * @property $commentable {@property-type relation}
 */
class ModelActionGetItemsFilterByMorphRelationTestComment extends Model
{

    protected $table = 'comments';
    protected $guarded = [];
    protected $fillable = [];

    public function getModelMetadata(): ModelMetadata
    {
        return new ModelMetadata(static::class);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

}
