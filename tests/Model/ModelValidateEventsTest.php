<?php

namespace Egal\Tests\Model;

use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Egal\Tests\DatabaseSchema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Laravel\Lumen\Application;
use PHPUnit\Framework\TestCase;

class ModelValidateEventsTest extends TestCase
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

    }

    protected function dropSchema(): void
    {
        $this->schema()->drop('products');
    }

    public function testValidateEventsDataProvider()
    {
        return [
            [ ['id' => 1, 'name' => 'first_product', 'count' => 1], true ],
            [ ['id' => 2, 'name' => 'second_product', 'count' => 2], false]
        ];
    }

    /**
     * @dataProvider testValidateEventsDataProvider()
     */
    public function testValidateKey(array $attributes, bool $isAction)
    {
        $app = new Application(dirname(__DIR__));
        $app->withFacades();

        Event::fake();

        if ($isAction) {
            ModelValidateEventsTestProductStub::actionCreate($attributes);
            Event::assertDispatched(ModelValidateEventsTestValidatingEvent::class);
            Event::assertDispatched(ModelValidateEventsTestValidatedEvent::class);
            Event::assertDispatched(ModelValidateEventsTestValidatingWithActionEvent::class);
            Event::assertDispatched(ModelValidateEventsTestValidatedWithActionEvent::class);
        } else {
            $object = new ModelValidateEventsTestProductStub();
            $object->fill($attributes);
            $object->save();
            Event::assertDispatched(ModelValidateEventsTestValidatingEvent::class);
            Event::assertDispatched(ModelValidateEventsTestValidatedEvent::class);
            Event::assertNotDispatched(ModelValidateEventsTestValidatingWithActionEvent::class);
            Event::assertNotDispatched(ModelValidateEventsTestValidatedWithActionEvent::class);
        }
    }

}

class ModelValidateEventsTestProductStub extends Model
{

    protected $table = 'products';
    protected $guarded = [];
    protected $fillable = [];

    protected $dispatchesEvents = [
        'validating.action' => ModelValidateEventsTestValidatingWithActionEvent::class,
        'validated.action' => ModelValidateEventsTestValidatedWithActionEvent::class,
        'validating' => ModelValidateEventsTestValidatingEvent::class,
        'validated' => ModelValidateEventsTestValidatedEvent::class,
    ];

    public function getModelMetadata(): ModelMetadata
    {
        return new ModelMetadata(static::class);
    }

}

class ModelValidateEventsTestValidatingWithActionEvent extends Event
{

}

class ModelValidateEventsTestValidatedWithActionEvent extends Event
{

}

class ModelValidateEventsTestValidatingEvent extends Event
{

}

class ModelValidateEventsTestValidatedEvent extends Event
{

}
