<?php

namespace Egal\Tests\Model;

use Carbon\Carbon;
use Egal\Model\Filter\FilterConditions\SimpleFilterConditionApplier;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Egal\Tests\DatabaseSchema;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;

class ModelActionGetItemsWithEmptyArrayPaginationParamsAndHiddenFieldsTest extends TestCase
{

    use DatabaseSchema;

    protected function createSchema(): void
    {
        $this->schema()->dropIfExists('products');

        $this->schema()->create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('count');
            $table->integer('sale')->nullable();
            $table->timestamps();
        });

        $productsAttributes = [
            ['id' => 1, 'name' => 'first_product', 'count' => 1],
            ['id' => 2, 'name' => 'second_product', 'count' => 2, 'sale' => 30],
            ['id' => 3, 'name' => 'product_third', 'count' => 3],
            ['id' => 4, 'name' => 'product_fourth', 'count' => 4, 'sale' => 50],
        ];

        foreach ($productsAttributes as $attributes) {
            ProductModelActionGetItemsWithNullPaginationAndHiddenFieldsTest::create($attributes);
        }
    }

    protected function dropSchema(): void
    {
        $this->schema()->drop('products');
    }

    public function test()
    {
        $actionResult = ProductModelActionGetItemsWithNullPaginationAndHiddenFieldsTest::actionGetItems([]);

        foreach ($actionResult['items'] as $item) {
            $item = $item->toArray();
            
            $this->assertArrayHasKey('name', $item);
            $this->assertArrayHasKey('sale', $item);
            $this->assertArrayNotHasKey('count', $item);
        }
    }

}

/**
 * @property int $id                           {@property-type field}  {@primary-key}
 * @property string $name       Название          {@property-type field}  {@validation-rules string}
 * @property string $count      Количество        {@property-type field}  {@validation-rules int}
 * @property string $sale       Скидка            {@property-type field}  {@validation-rules int}
 * @property Carbon $created_at                   {@property-type field}  {@validation-rules date}
 * @property Carbon $updated_at                   {@property-type field}  {@validation-rules date}
 *
 * @action create         {@statuses-access guest}
 * @action getItems       {@statuses-access guest}
 */
class ProductModelActionGetItemsWithNullPaginationAndHiddenFieldsTest extends Model
{

    protected $table = 'products';

    protected $fillable = [
        'name',
        'count',
        'sale',
    ];

    protected $hidden = [
        'count',
    ];

    public function getModelMetadata(): ModelMetadata
    {
        return new ModelMetadata(static::class);
    }

}
