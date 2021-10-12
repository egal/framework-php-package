<?php

namespace Egal\Tests\Model;

use Carbon\Carbon;
use Egal\Model\Exceptions\FieldNotFoundException;
use Egal\Model\Exceptions\UnsupportedFilterConditionException;
use Egal\Model\Exceptions\UnsupportedFilterValueTypeException;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Egal\Tests\DatabaseSchema;
use Illuminate\Database\Schema\Blueprint;
use Laravel\Lumen\Application;
use PHPUnit\Framework\TestCase;

class ModelFilterValidationTest extends TestCase
{
    use DatabaseSchema;

    protected function createSchema(): void
    {
        $this->schema()->create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('count');
            $table->timestamps();
        });

        $productsAttributes = [
            ['id' => 1, 'name' => 'first_product', 'count' => 1],
            ['id' => 2, 'name' => 'second_product', 'count' => 2],
            ['id' => 3, 'name' => 'product_third', 'count' => 3],
            ['id' => 4, 'name' => 'product_fourth', 'count' => 4],
        ];

        foreach ($productsAttributes as $attributes) {
            ModelFilterValidationTestModel::create($attributes);
        }
    }

    protected function dropSchema(): void
    {
        $this->schema()->drop('products');
    }

    public function productsValidationFilterDataProvider()
    {
        return [
            [
                [["name", "eq", "bar"]],
                null
            ],
            [
                [["names", "eq", "bar"]],
                FieldNotFoundException::class
            ],
            [
                [["name", "eq", 34]],
                UnsupportedFilterValueTypeException::class
            ],
            [
                [["name", "edq", "bar"]],
                UnsupportedFilterConditionException::class
            ],
            [
                [["created_at", "eq", "2021-10-00T11:24:07.000000Z"]],
                UnsupportedFilterValueTypeException::class
            ],
        ];
    }

    /**
     * @dataProvider productsValidationFilterDataProvider()
     */
    public function testProductsFilterValidation(array $filter, ?string $expectException)
    {
        if ($expectException) {
            $this->expectException($expectException);
        }

        ModelFilterValidationTestModel::actionGetItems(
            null,
            [],
            $filter,
            []
        );
    }

}

/**
 * @property int    $id                           {@property-type field}  {@prymary-key}
 * @property string $name       Название          {@property-type field}  {@validation-rules string}
 * @property string $count      Количество        {@property-type field}  {@validation-rules int}
 * @property Carbon $created_at                   {@property-type field}  {@validation-rules date}
 * @property Carbon $updated_at                   {@property-type field}  {@validation-rules date}
 *
 * @action create         {@statuses-access guest}
 * @action getItems       {@statuses-access guest}
 */
class ModelFilterValidationTestModel extends Model
{

    protected $table = 'products';

    protected $fillable = [
      'name',
      'count'
    ];

    public function getModelMetadata(): ModelMetadata
    {
        return new ModelMetadata(static::class);
    }

}
