<?php

namespace EgalFramework\Model\Tests;

use EgalFramework\Common\Session;
use EgalFramework\Model\Deprecated\NotFoundException;
use EgalFramework\Model\Tests\Samples\Stubs\FilterQuery;
use EgalFramework\Model\Tests\Samples\Stubs\Message;
use EgalFramework\Model\Tests\Samples\Models\Country;
use EgalFramework\Model\Tests\Samples\Stubs\ModelManager;
use Exception;
use EgalFramework\Model\Tests\Samples\Models\Test;
use EgalFramework\Model\Deprecated\ValidateException;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

class CRUDTest extends TestCase
{

    /** @var Builder|Test */
    private $model;

    private Test $entity1;

    private Test $entity2;

    /**
     * @throws ValidateException
     */
    protected function setUp(): void
    {
        parent::setUp();
        Session::setModelManager(new ModelManager);
        Session::setValidateCallback(function ($fields, $rules) {
            if (in_array('content', $rules) && empty($fields['content'])) {
                throw new ValidateException();
            }
            return [];
        });
        Session::setFilterQuery(new FilterQuery);
        Session::setMessage(new Message);
        $this->model = new Test;

        $this->setUpDB();
        $this->setUpRecords();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function setUpDB()
    {
        DB::schema()->create('tests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('caption');
            $table->longText('content');
            $table->bigInteger('country_id')->default(0);
            $table->boolean('is_important')->default(FALSE);
            $table->date('date_field')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->time('time_field')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('email')->default('');
            $table->string('password')->default('');
            $table->integer('int_field')->default(0);
            $table->integer('list_field')->default(0);
            $table->float('float_field')->default(0);
            $table->boolean('bool_field')->default(false);
            $table->string('hash');
            $table->timestamps();
        });
        DB::schema()->create('countries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('hash');
            $table->timestamps();
        });
    }

    /**
     * @throws ValidateException
     */
    private function setUpRecords()
    {
        try {
            $country = new Country();
            $country->name = 'Country1';
            $country->save();
            $country = new Country();
            $country->name = 'Country2';
            $country->save();
            /** @var Test $entity1 */
            $this->entity1 = $this->model->create([
                'caption' => 'Test1',
                'content' => Str::random(200),
                'bool_field' => true,
                'country_id' => 1,
                'list_field' => 2,
            ]);
            /** @var Test $entity2 */
            $this->entity2 = $this->model->create([
                'caption' => 'Test2',
                'content' => Str::random(200),
                'bool_field' => true,
                'country_id' => 2,
                'list_field' => 3,
            ]);
        } catch (ValidateException $e) {
            throw $e;
        }
    }

    protected function tearDown(): void
    {
        DB::schema()->dropIfExists('tests');
        DB::schema()->dropIfExists('countries');
        parent::tearDown();
    }

    public function testCreate()
    {
        /** @var Test $entity1Check */
        $entity1Check = $this->model->find($this->entity1->id);
        /** @var Test $entity2Check */
        $entity2Check = $this->model->find($this->entity2->id);
        $this->assertEquals($this->entity1->id, $entity1Check->id);
        $this->assertEquals($this->entity1->caption, $entity1Check->caption);
        $this->assertEquals($this->entity2->id, $entity2Check->id);
        $this->assertEquals($this->entity2->caption, $entity2Check->caption);
    }

    /**
     * @throws ValidateException
     */
    public function testException()
    {
        $this->expectException(ValidateException::class);
        $this->model->create(['caption' => 'TestFail']);
    }

    /**
     * @throws Exception
     */
    public function testRead()
    {
        $items = $this->model->getItems();
        $this->assertTrue(count($items) > 1);

        Session::getMessage()->setId(2);
        $item = $this->model->getItem();
        $this->assertNotEmpty($item);

        $this->expectException(NotFoundException::class);
        Session::getMessage()->setId(123);
        $this->model->getItem();
    }

    /**
     * @throws ValidateException
     */
    public function testUpdate()
    {
        /** @var Test $item */
        $item = $this->model->where('caption', 'ILIKE', 'Test1')->first();
        $item->update(['caption' => 'Test3', 'fake_field' => 'zzz']);
        $item = $this->model->where('caption', 'ILIKE', 'Test3')->get()->get(0);
        $this->assertEquals('Test3', $item->caption);
    }

    /**
     * @throws Exception
     */
    public function testDelete()
    {
        $ids = [];
        /** @var Test $item */
        foreach ($this->model->where('caption', 'ILIKE', 'Test%')->get() as $item) {
            $item->delete();
        }
        $this->assertEquals(0, $this->model->whereIn('id', $ids)->count());
    }

}
