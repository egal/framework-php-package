<?php

namespace Egal\Tests\Model;

use Carbon\Carbon;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Egal\Tests\DatabaseSchema;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Schema\Blueprint;
use Laravel\Lumen\Application;
use PHPUnit\Framework\TestCase;

class ModelActionGetItemsFilterWithsTest extends TestCase
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

        ModelActionGetItemsFilterWithsTestCategoryStub::create(['id' => 1]);
        ModelActionGetItemsFilterWithsTestProductStub::create(['id' => 1, 'category_id' => 1]);
        ModelActionGetItemsFilterWithsTestProductStub::create(['id' => 2, 'category_id' => 1]);
        ModelActionGetItemsFilterWithsTestProductStub::create(['id' => 3, 'category_id' => 1]);
    }

    protected function dropSchema(): void
    {
        $this->schema()->drop('products');
        $this->schema()->drop('categories');
    }

    public function dataProviderFilterWiths()
    {
        return [
            [
                ['products'],
                null,
                [1, 2, 3],
            ],
            [
                ['products' => []],
                null,
                [1, 2, 3],
            ],
            [
                [
                    'products' => [
                        'filter' => [
                            ['id', 'eq', 1],
                        ]
                    ]
                ],
                null,
                [1],
            ],
            [
                [
                    'products' => [
                        'filter' => [
                            ['id', 'eq', 1],
                            'OR',
                            ['id', 'eq', 2],
                        ]
                    ]
                ],
                null,
                [1, 2],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderFilterWiths
     */
    public function testFilterWiths(?array $withs, ?string $expectException, $equalsExpect)
    {
        if ($expectException) {
            $this->expectException($expectException);
        }

        $actual = array_column(ModelActionGetItemsFilterWithsTestCategoryStub::actionGetItems(
            null,
            $withs,
            [['id', 'eq', 1]],
            []
        )['items'][0]['products'], 'id');

        if ($equalsExpect) {
            $this->assertEquals($equalsExpect, $actual);
        }
    }

}

/**
 * @property int    $id                           {@property-type field}  {@primary-key}
 * @property ModelActionGetItemsFilterWithsTestProductStub[] $products {@property-type relation}
 *
 * @action create         {@statuses-access guest}
 * @action getItems       {@statuses-access guest}
 */
class ModelActionGetItemsFilterWithsTestCategoryStub extends Model
{

    protected $table = 'categories';
    protected $guarded = [];

    public function getModelMetadata(): ModelMetadata
    {
        return new ModelMetadata(static::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(ModelActionGetItemsFilterWithsTestProductStub::class, 'category_id', 'id');
    }

}

/**
 * @property int    $id                           {@property-type field}  {@primary-key}
 * @property int    $category_id                  {@property-type field}  {@validation-rules int}
 * @property ModelActionGetItemsFilterWithsTestProductStub[] $products {@property-type relation}
 *
 * @action create         {@statuses-access guest}
 * @action getItems       {@statuses-access guest}
 */
class ModelActionGetItemsFilterWithsTestProductStub extends Model
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
        return $this->belongsTo(ModelActionGetItemsFilterWithsTestCategoryStub::class);
    }

}

