<?php

namespace Egal\Tests\Model;

use Egal\Model\Builder;
use Egal\Model\Exceptions\FieldNotFoundException;
use Egal\Model\Exceptions\UnsupportedFilterConditionException;
use Egal\Model\Exceptions\UnsupportedFilterValueTypeException;
use Egal\Model\Filter\FilterCondition;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Egal\Tests\DatabaseSchema;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;

class ModelFilterValidationTest extends TestCase
{
    use DatabaseSchema;

    protected function createSchema(): void
    {
        $this->schema()->create('models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('string');
            $table->integer('integer');
            $table->double('numeric');
            $table->boolean('boolean');
            $table->json('array');
            $table->json('json');
            $table->timestamps();
        });

        $productsAttributes = [
            ['id' => 1, 'string' => 'first', 'integer' => 1, 'numeric' => 1.1, 'boolean' => true, 'array' => ['first'], 'json' => '{"first":["first"]}'],
            ['id' => 2, 'string' => 'second', 'integer' => 2, 'numeric' => 2.2, 'boolean' => false, 'array' => ['second'], 'json' => '{"second":["second"]}'],
            ['id' => 3, 'string' => 'third', 'integer' => 3, 'numeric' => 3.3, 'boolean' => true, 'array' => ['third'], 'json' => '{"third":["third"]}'],
            ['id' => 4, 'string' => 'fourth', 'integer' => 4, 'numeric' => 4.4, 'boolean' => false, 'array' => ['fourth'], 'json' => '{"fourth":["fourth"]}'],
        ];

        foreach ($productsAttributes as $attributes) {
            ModelFilterValidationTestModel::create($attributes);
        }
    }

    protected function dropSchema(): void
    {
        $this->schema()->drop('models');
    }

    public function productsValidationFilterDataProvider()
    {
        return [
            [
                [['foo', 'eq', 'bar']],
                FieldNotFoundException::class,
            ],
            [
                [['string', 'eq', 'bar']],
                null,
            ],
            [
                [['string', 'eq', 34]],
                UnsupportedFilterValueTypeException::class,
            ],
            [
                [['string', 'edq', 'bar']],
                UnsupportedFilterConditionException::class,
            ],
            [
                [['created_at', 'eq', '2021-10-00T11:24:07.000000Z']],
                UnsupportedFilterValueTypeException::class,
            ],
            [
                [['created_at', 'eq', '2021-10-01T11:24:07.000000Z']],
                null,
            ],
            [
                [['integer', 'eq', 2]],
                null,
            ],
            [
                [['integer', 'eq', 'two']],
                UnsupportedFilterValueTypeException::class,
            ],
            [
                [['boolean', 'eq', true]],
                null,
            ],
            [
                [['boolean', 'eq', 'true']],
                UnsupportedFilterValueTypeException::class,
            ],
            [
                [['numeric', 'eq', 1.1]],
                null,
            ],
            [
                [['numeric', 'eq', '0x539']],
                UnsupportedFilterValueTypeException::class,
            ],
            [
                [['array', 'foo', ['foo']]],
                null,
            ],
            [
                [['array', 'eq', 'foo']],
                UnsupportedFilterValueTypeException::class,
            ],
            [
                [['json', 'foo', '{"fourth":["fourth"]}']],
                null,
            ],
            [
                [['json', 'eq', 'foo']],
                UnsupportedFilterValueTypeException::class,
            ],
            [
                [['fake', 'eq', 'foo']],
                FieldNotFoundException::class,
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

        ModelFilterValidationTestModel::actionGetItems(null, [], $filter, []);
    }

}

/**
 * @property $id            {@property-type field}       {@primary-key}
 * @property $string        {@property-type field}       {@validation-rules string}
 * @property $integer       {@property-type field}       {@validation-rules integer}
 * @property $numeric       {@property-type field}       {@validation-rules numeric}
 * @property $boolean       {@property-type field}       {@validation-rules boolean}
 * @property $array         {@property-type field}       {@validation-rules array}
 * @property $json          {@property-type field}       {@validation-rules json}
 * @property $fake          {@property-type fake-field}  {@validation-rules json}
 * @property $created_at    {@property-type field}       {@validation-rules date}
 * @property $updated_at    {@property-type field}       {@validation-rules date}
 */
class ModelFilterValidationTestModel extends Model
{

    protected $table = 'models';
    protected $guarded = [];
    protected $fillable = [];
    protected $casts = [
        'array' => 'array',
    ];

    public function getModelMetadata(): ModelMetadata
    {
        return new ModelMetadata(static::class);
    }
    public static function applyFooFilterCondition(Builder &$builder, FilterCondition $condition, string $beforeOperator): void
    {

    }

}
