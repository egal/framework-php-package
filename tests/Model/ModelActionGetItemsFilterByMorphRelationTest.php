<?php

namespace Egal\Tests\Model;

use Carbon\Carbon;
use Closure;
use Egal\Model\Builder;
use Egal\Model\Exceptions\RelationNotFoundException;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Egal\Tests\DatabaseSchema;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Schema\Blueprint;
use Laravel\Lumen\Application;
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
            $table->integer('sale')->nullable();
            $table->timestamps();
        });
        ModelActionGetItemsFilterByMorphRelationTestProduct::create([
            'id' => 1,
            'name' => 'first',
        ]);
        ModelActionGetItemsFilterByMorphRelationTestProduct::create([
            'id' => 2,
            'name' => 'second',
            'sale' => 30
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
        ModelActionGetItemsFilterByMorphRelationTestComment::create([
            'id' => 3,
            'commentable_type' => ModelActionGetItemsFilterByMorphRelationTestProduct::class,
            'commentable_id' => 2,
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
                [1, 2, 3]
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
            [
                [
                    ['commentable[' . ModelActionGetItemsFilterByMorphRelationTestProduct::class . '].sale', 'ne', null],
                ],
                null,
                [3]
            ],
            [
                [
                    ['commentable[' . ModelActionGetItemsFilterByMorphRelationTestProduct::class . '].created_at', 'le', Carbon::now()->toDateTimeString()],
                ],
                null,
                [1, 3]
            ]
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

/**
 * @property int    $id                           {@property-type field}  {@primary-key}
 * @property string $name       Название          {@property-type field}  {@validation-rules string}
 * @property string $count      Количество        {@property-type field}  {@validation-rules int}
 * @property string $sale       Скидка            {@property-type field}  {@validation-rules int}
 * @property Carbon $created_at                   {@property-type field}  {@validation-rules date}
 * @property Carbon $updated_at                   {@property-type field}  {@validation-rules date}
 * @property ModelActionGetItemsFilterByMorphRelationTestComment $comment {@property-type relation}
 *
 * @action create         {@statuses-access guest}
 * @action getItems       {@statuses-access guest}
 */
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

/**
 * @property int    $id                           {@property-type field}  {@primary-key}
 * @property Carbon $created_at                   {@property-type field}  {@validation-rules date}
 * @property Carbon $updated_at                   {@property-type field}  {@validation-rules date}
 * @property ModelActionGetItemsFilterByMorphRelationTestComment $comment {@property-type relation}
 *
 * @action create         {@statuses-access guest}
 * @action getItems       {@statuses-access guest}
 */
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
 * @property int    $id                           {@property-type field}  {@primary-key}
 * @property Carbon $created_at                   {@property-type field}  {@validation-rules date}
 * @property Carbon $updated_at                   {@property-type field}  {@validation-rules date}
 * @property        $commentable                  {@property-type relation}
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
