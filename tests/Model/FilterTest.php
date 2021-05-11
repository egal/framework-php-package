<?php

namespace EgalFramework\Model\Tests;

use EgalFramework\Common\Session;
use EgalFramework\Model\Tests\Samples\Models\Test;
use EgalFramework\Model\Tests\Samples\Stubs\FilterQuery;
use EgalFramework\Model\Tests\Samples\Stubs\Message;
use EgalFramework\Model\Tests\Samples\Stubs\ModelManager;
use Exception;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{

    const EMAIL = 'nobody@example.com#phpunit';

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        Session::setModelManager(new ModelManager);
        Session::setFilterQuery(new FilterQuery);
        $this->setUpDB();
        for ($i = 0; $i < 100; $i++) {
            $this->generateTestModels($i);
        }
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
    }

    /**
     * @param int $id
     */
    private function generateTestModels(int $id)
    {
        $item = new Test;
        $item->caption = 'Test123' . $id;
        $item->content = Str::random(200);
        $item->country_id = rand(1, 10);
        $item->is_important = false;
        $item->date_field = date('Y-m-d', rand(0, 99999999));
        $item->time_field = date('H:i:s', rand(0, 99999999));
        $item->email = self::EMAIL;
        $item->password = Str::random();
        $item->int_field = $id;
        $item->list_field = rand(0, 10);
        $item->float_field = rand(10, 11000000) / 100;
        $item->bool_field = rand(0, 1);
        $item->save();
    }

    protected function tearDown(): void
    {
        DB::schema()->dropIfExists('tests');
        parent::tearDown();
    }

    /**
     * @param array $data
     * @param bool $asc
     * @throws Exception
     * @dataProvider dataProviderOrder
     */
    public function testOrder(array $data, bool $asc = TRUE)
    {
        $result = $this->getModel($data)->getItems();
        $id = $asc
            ? 0
            : 999999999999;
        foreach ($result['items'] as $item) {
            if ($asc) {
                $this->assertGreaterThanOrEqual($id, $item['int_field']);
            } else {
                $this->assertLessThanOrEqual($id, $item['int_field']);
            }
            $id = $item['int_field'];
        }
    }

    /**
     * @return array
     */
    public function dataProviderOrder()
    {
        return [
            [
                [
                    '_order_by' => 'int_field',
                    '_order' => 'asc',
                ], TRUE
            ],
            [
                [
                    '_order_by' => 'int_field',
                    '_order' => 'desc',
                ], FALSE
            ],
        ];
    }

    /**
     * @param array $data
     * @param int $count
     * @throws Exception
     * @dataProvider dataProviderSearch
     */
    public function testSearch(array $data, int $count)
    {
        $result = $this->getModel($data)->getItems();
        $this->assertEquals($count, $result['count']);
        $this->assertCount($count, $result['items']);
    }

    /**
     * @return array
     */
    public function dataProviderSearch()
    {
        return [
            [['int_field' => 1], 1],
            [['_search' => json_encode(['int_field' => '1']), '_count' => 1000], 19],
        ];
    }

    /**
     * @param array $data
     * @param int $count
     * @throws Exception
     * @dataProvider dataProviderFromTo
     */
    public function testFromTo(array $data, int $count)
    {
        $result = $this->getModel($data)->getItems();
        $this->assertEquals(100, $result['count']);
        $this->assertCount($count, $result['items']);
    }

    /**
     * @return array
     */
    public function dataProviderFromTo()
    {
        return [
            [
                [
                    '_from' => 0,
                    '_count' => 32,
                ], 32
            ],
            [
                [
                    '_from' => 100,
                    '_count' => 32,
                ], 0
            ],
            [
                [
                    '_from' => 0,
                    '_count' => 1000,
                ], 100
            ],
            [
                [
                    '_from' => 55,
                    '_count' => 1000,
                ], 45
            ],
            [
                [], 10
            ],
        ];
    }

    /**
     * @param array $data
     * @param int $count
     * @throws Exception
     * @dataProvider dataProviderRange
     */
    public function testRange(array $data, int $count)
    {
        $result = $this->getModel($data)->getItems();
        $this->assertEquals($count, $result['count']);
        $this->assertCount($count, $result['items']);
    }

    public function dataProviderRange()
    {
        return [
            [
                [
                    '_range_from' => json_encode(['int_field' => 10]),
                    '_range_to' => json_encode(['int_field' => 18]),
                ], 9
            ],
            [
                [
                    '_range_from' => json_encode(['int_field' => 22]),
                    '_range_to' => json_encode(['int_field' => 20]),
                ], 0
            ],
            [
                [
                    '_range_from' => json_encode(['int_field' => 32]),
                    '_range_to' => json_encode(['int_field' => 33]),
                ], 2
            ],
            [
                [
                    '_range_from' => json_encode(['int_field' => 10]),
                    '_range_to' => json_encode(['int_field' => 10]),
                ], 1
            ],
        ];
    }

    /**
     * @dataProvider dataProviderFieldSearch
     * @param array $data
     * @param int $cnt
     * @param bool $fullSearch
     * @throws Exception
     */
    public function testFieldSearch(array $data, int $cnt, bool $fullSearch)
    {
        if ($fullSearch) {
            Session::getMetadata('Test')->setSupportFullSearch();
        }
        $result = $this->getModel($data)->getItems();
        $this->assertEquals($cnt, $result['count']);
    }

    public function dataProviderFieldSearch()
    {
        return [
            [
                [
                    'int_field' => '[3, 10]',
                    '_count' => 0,
                ], 2, false,
            ],
            [
                [
                    '_full_search' => 'qweqwe',
                    'int_field' => '[3, 10]',
                ], 2, false
            ],
            [
                [
                    '_full_search' => 'Test12310',
                    'int_field' => '[3, 10]',
                ], 1, true
            ],
            [
                [
                    '_full_search' => '',
                    'int_field' => '[3, 10]',
                ], 2, true
            ],
        ];
    }

    /**
     * @param array $data
     * @param int $cnt
     * @throws Exception
     * @dataProvider dataProviderFullSearch
     */
    public function testFullSearch(array $data, int $cnt)
    {
        $result = $this->getModel($data)->getItems();
        $this->assertEquals($cnt, $result['count']);
    }

    public function dataProviderFullSearch()
    {
        return [
            [
                [
                    '_full_search' => 'phpunit',
                    'int_field' => '[3,10]',
                ], 2
            ],
        ];
    }

    /**
     * @param array $data
     * @return Test
     */
    private function getModel(array $data): Test
    {
        $data['email'] = self::EMAIL;
        $message = new Message;
        $message->setQuery($data);
        Session::setMessage($message);
        return new Test;
    }

}
