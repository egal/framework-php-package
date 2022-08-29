<?php

namespace Egal\Tests\Model;

use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Egal\Tests\DatabaseSchema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Laravel\Lumen\Application;
use PHPUnit\Framework\TestCase;

class FireActionModelEventsTest extends TestCase
{
    use DatabaseSchema;

    protected function createSchema(): void
    {
        $this->schema()->dropIfExists('products');
        $this->schema()->create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        $productsAttributes = [
            ['name' => 'first_product'],
            ['name' => 'second_product'],
            ['name' => 'product_third'],
            ['name' => 'product_fourth'],
        ];

        foreach ($productsAttributes as $attributes) {
            FireActionModelEventsTestProductStub::create($attributes);
        }
    }

    protected function dropSchema(): void
    {
        $this->schema()->drop('products');
    }

    public function testActionEventsDataProvider()
    {
        return [
            [
                FireActionModelEventsTestRetrievedWithActionEvent::class,
                fn() => FireActionModelEventsTestProductStub::actionGetItems(),
                FireActionModelEventsTestSavedWithActionEvent::class
            ],
            [
                FireActionModelEventsTestSavedWithActionEvent::class,
                fn() => FireActionModelEventsTestProductStub::actionUpdate(1, ['name' => 'saved_product']),
                null
            ],
            [
                FireActionModelEventsTestCreatingWithActionEvent::class,
                fn() => FireActionModelEventsTestProductStub::actionCreate(['name' => 'created_product']),
                FireActionModelEventsTestUpdatedWithActionEvent::class
            ],
            [
                FireActionModelEventsTestUpdatedWithActionEvent::class,
                fn() => FireActionModelEventsTestProductStub::actionUpdate(1, ['name' => 'updated_product']),
                null
            ],
            [
                FireActionModelEventsTestDeletingWithActionEvent::class,
                fn() => FireActionModelEventsTestProductStub::actionDelete(1),
                null
            ]
        ];
    }

    /**
     * @dataProvider testActionEventsDataProvider
     * @group current
     */
    public function testFireActionModelEvents($expectedEvent, $function, $unexpectedEvent = null)
    {
        $app = new Application(dirname(__DIR__));
        $app->withFacades();

        Event::fake();

        call_user_func($function);

        Event::assertDispatched($expectedEvent);
        if (isset($unexpectedEvent)) {
            Event::assertNotDispatched($unexpectedEvent);
        }
    }
}

class FireActionModelEventsTestProductStub extends Model
{

    protected $table = 'products';
    protected $guarded = [];
    protected $fillable = [];

    protected $dispatchesEvents = [
        'retrieved.action' => FireActionModelEventsTestRetrievedWithActionEvent::class,
        'saved.action' => FireActionModelEventsTestSavedWithActionEvent::class,
        'creating.action' => FireActionModelEventsTestCreatingWithActionEvent::class,
        'updated.action' => FireActionModelEventsTestUpdatedWithActionEvent::class,
        'deleting.action' => FireActionModelEventsTestDeletingWithActionEvent::class,
    ];

    public function getModelMetadata(): ModelMetadata
    {
        return new ModelMetadata(static::class);
    }

}

class FireActionModelEventsTestRetrievedWithActionEvent extends Event
{

}

class FireActionModelEventsTestSavedWithActionEvent extends Event
{

}

class FireActionModelEventsTestCreatingWithActionEvent extends Event
{

}

class FireActionModelEventsTestUpdatedWithActionEvent extends Event
{

}

class FireActionModelEventsTestDeletingWithActionEvent extends Event
{

}
