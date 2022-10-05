<?php

namespace Egal\Tests\Model;

use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Application;
use Egal\Model\Exceptions\ExceedingTheLimitCountEntitiesForManipulationException;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Egal\Tests\DatabaseSchema;
use Illuminate\Database\Schema\Blueprint;
use PDOException;
use PHPUnit\Framework\TestCase;

class ModelHasDefaultLimitsTest extends TestCase
{
    use DatabaseSchema;

    protected function createSchema(): void
    {
        $this->schema()->create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        $this->schema()->create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });
    }

    protected function dropSchema(): void
    {
        $this->schema()->drop('products');

        $this->schema()->drop('orders');

    }

    public function dataProviderHasDefaultLimits()
    {
        return [
            [
                [
                    ['name' => '1'],
                    ['name' => '2'],
                    ['name' => '3'],
                    ['name' => '4'],
                    ['name' => '5'],
                    ['name' => '6'],
                    ['name' => '7'],
                    ['name' => '8'],
                    ['name' => '9'],
                    ['name' => '10']
                ],
                ModelHasDefaultLimitsTestProductStub::class,
                null,
            ],
            [
                [
                    ['name' => '1'],
                    ['name' => '2'],
                    ['name' => '3'],
                    ['name' => '4'],
                    ['name' => '5'],
                    ['name' => '6'],
                    ['name' => '7'],
                    ['name' => '8'],
                    ['name' => '9'],
                    ['name' => '10'],
                    ['name' => '11']
                ],
                ModelHasDefaultLimitsTestProductStub::class,
                ExceedingTheLimitCountEntitiesForManipulationException::class,
            ],
            [
                [
                    ['name' => '1'],
                    ['name' => '2'],
                    ['name' => '3'],
                    ['name' => '4'],
                    ['name' => '5'],
                    ['name' => '6'],
                    ['name' => '7'],
                    ['name' => '8'],
                    ['name' => '9'],
                    ['name' => '10'],
                    ['name' => '11'],
                    ['name' => '12'],
                    ['name' => '13'],
                    ['name' => '14'],
                    ['name' => '15']
                ],
                ModelHasDefaultLimitsTestOrderStub::class,
                null,
            ],
            [
                [
                    ['name' => '1'],
                    ['name' => '2'],
                    ['name' => '3'],
                    ['name' => '4'],
                    ['name' => '5'],
                    ['name' => '6'],
                    ['name' => '7'],
                    ['name' => '8'],
                    ['name' => '9'],
                    ['name' => '10'],
                    ['name' => '11'],
                    ['name' => '12'],
                    ['name' => '13'],
                    ['name' => '14'],
                    ['name' => '15'],
                    ['name' => '16']
                ],
                ModelHasDefaultLimitsTestOrderStub::class,
                ExceedingTheLimitCountEntitiesForManipulationException::class,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderHasDefaultLimits
     */
    public function testHasDefaultLimits($data, $model, $expectException)
    {
        $app = new Application(dirname(__DIR__));
        $app->withFacades();

        if ($expectException) {
            $this->expectException($expectException);
        }

        try {
            $model::actionCreateMany($data);
        } catch (PDOException $exception) {

        }
    }

}

class ModelHasDefaultLimitsTestProductStub extends Model
{

    protected $table = 'products';
    protected $guarded = [];
    protected $fillable = [];

    public function getModelMetadata(): ModelMetadata
    {
        return new ModelMetadata(static::class);
    }

}

class ModelHasDefaultLimitsTestOrderStub extends Model
{

    protected int $maxCountEntitiesToProcess = 15;

    protected $table = 'orders';
    protected $guarded = [];
    protected $fillable = [];

    public function getModelMetadata(): ModelMetadata
    {
        return new ModelMetadata(static::class);
    }

}
