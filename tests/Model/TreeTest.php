<?php

namespace EgalFramework\Model\Tests;

use EgalFramework\Common\Session;
use EgalFramework\Model\Tests\Samples\Models\Country;
use EgalFramework\Model\Tests\Samples\Models\Test;
use EgalFramework\Model\Tests\Samples\Stubs\Direction;
use EgalFramework\Model\Tests\Samples\Stubs\FilterQuery;
use EgalFramework\Model\Tests\Samples\Stubs\Message;
use EgalFramework\Model\Tests\Samples\Stubs\ModelManager;
use EgalFramework\Model\Deprecated\Tree;
use EgalFramework\Model\Deprecated\ValidateException;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

class TreeTest extends TestCase
{

    private Test $model;

    private array $treeWithDirection = [
        [
            'id' => '1',
            'label' => 'first',
            'direction' => [
                'model' => 'test_model',
                'id' => '0'
            ]
        ]
    ];

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

            $this->model->create([
                'caption' => 'Test1',
                'content' => Str::random(200),
                'bool_field' => true,
                'country_id' => 1,
                'list_field' => 2,
            ]);

            $this->model->create([
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

    public function testOneToOne()
    {
        Session::setModelManager(new ModelManager);
        $model = new Test;
        $this->assertEquals(true, true);
    }

    public function testOneToMany()
    {
        $this->assertTrue(true);
    }

    public function testManyToMany()
    {
        $this->assertTrue(true);
    }

    public function testBelongsTo()
    {
        $this->assertTrue(true);
    }

    /**
     * @dataProvider addDataProvider
     * @param $expected
     * @param $id
     * @param $label
     */
    public function testAdd($expected, $id, $label)
    {
        $tree = new Tree();
        $subTree = $tree->add($id, $label);

        $this->assertInstanceOf(Tree::class, $subTree);
        $this->assertEquals($expected, $tree->toArray());
    }

    public function addDataProvider()
    {
        return [
            // $expected, $id, $label
            [[[]], 0, 'zero'],
            [[['id' => '1', 'label' => 'first']], 1, 'first'],
        ];
    }

    public function testGetId()
    {
        $tree = new Tree(777, 'some_label');
        $this->assertEquals(777, $tree->getId());
    }

    public function testSetDirection()
    {
        $tree = new Tree();
        $subTree = $tree->add(1, 'first');

        $direction = new Direction('test_model', 0);
        $returnedTree = $subTree->setDirection($direction);
        $this->assertEquals($subTree, $returnedTree);
        $this->assertEquals($this->treeWithDirection, $tree->toArray());
    }

    /**
     * @dataProvider toArrayDataProvider
     * @param $expectedResult
     * @param $id
     * @param $topId
     * @param $direction
     */
    public function testToArrayOneNode($expectedResult, $id, $topId, $direction)
    {
        $tree = new Tree($id, 'root');

        if ($direction) {
            $tree->setDirection($direction);
        }

        $this->assertEquals($expectedResult, $tree->toArray($topId));
    }

    public function toArrayDataProvider()
    {
        return [
            // $expectedResult, $id, $topId, $direction
            [[], 0, 0, null],
            [[], 0, 1, null],
            [['id' => 1, 'label' => 'root'], 1, 0, null],
        ];
    }

    public function testToArrayFewNodes()
    {
        $tree = new Tree(0, 'root');

        $subTree = $tree->add(1, 'first');
        $subSubTree = $subTree->add(2, 'second');

        $direction = new Direction('test_model', 1);
        $subSubTree->setDirection($direction);

        $result = $tree->toArray();
        $uuidValue = $result[0]['children'][0]['id'];

        $this->assertStringMatchesFormat('%x', str_replace('-', '', $uuidValue));
        $this->assertEquals(36, strlen($uuidValue));

        $result[0]['children'][0]['id'] = 'uuid_value';

        $this->assertEquals([
            [
                'id' => '1',
                'label' => 'first',
                'children' => [
                    [
                        'id' => 'uuid_value',
                        'label' => 'second',
                        'topId' => '1',
                        'direction' => [
                            'model' => 'test_model',
                            'id' => '1'
                        ]
                    ]
                ],
            ]
        ],
            $result);
    }
}
