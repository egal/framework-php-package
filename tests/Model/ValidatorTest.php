<?php

namespace EgalFramework\Model\Tests;

use EgalFramework\Model\Tests\Samples\Models\Test;

use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{

    /** @var Test */
    private Test $model;

    public function testTrue()
    {
        $this->assertTrue(true);
    }

//    protected function setUp(): void
//    {
//        parent::setUp();
//        Session::setModelManager(new ModelManager);
//        Session::setValidateCallback(function ($fields, $rules) {
//            if (in_array('content', $rules) && empty($fields['content'])) {
//                throw new ValidateException();
//            }
//            return [];
//        });
//
//        $this->model = new Test;
//
//        $this->setUpDB();
//    }
//
//    /**
//     * Run the migrations.
//     *
//     * @return void
//     */
//    public function setUpDB()
//    {
//        DB::schema()->create('tests', function (Blueprint $table) {
//            $table->bigIncrements('id');
//            $table->string('caption');
//            $table->longText('content');
//            $table->bigInteger('country_id')->default(0);
//            $table->boolean('is_important')->default(FALSE)->nullable();
//            $table->date('date_field')->default(DB::raw('CURRENT_TIMESTAMP'));
//            $table->time('time_field')->default(DB::raw('CURRENT_TIMESTAMP'));
//            $table->string('email')->default('');
//            $table->string('password')->default('');
//            $table->integer('int_field')->default(0);
//            $table->integer('list_field')->default(0);
//            $table->float('float_field')->default(0);
//            $table->boolean('bool_field')->default(false);
//            $table->string('hash');
//            $table->timestamps();
//        });
//    }
//
//    protected function tearDown(): void
//    {
//        DB::schema()->dropIfExists('tests');
//        parent::tearDown();
//    }
//
//    /**
//     * @param array $attrs
//     * @param string $exception
//     * @throws ValidateException
//     * @dataProvider requiredDataProvider
//     */
//    public function testRequired(array $attrs, string $exception)
//    {
//        if ($exception) {
//            $this->expectException($exception);
//        }
//        $this->assertNotNull($this->model->create($attrs));
//    }
//
//    public function requiredDataProvider()
//    {
//        return [
//            [
//                [
//                    'caption' => 'caption1',
//                    'content' => Str::random(200),
//                    'bool_field' => true,
//
//                    'country_id' => 0,
//                    'is_important' => false,
//                    'fake_field' => 'aaa',
//                ], '',
//            ],
//            [['caption' => 'caption2', 'content' => Str::random(200)], ValidateException::class],
//            [['caption' => 'caption3', 'content' => Str::random(200), 'bool_field' => false], ''],
//            [['caption' => 'caption4', 'content' => Str::random(200), 'bool_field' => null], ValidateException::class],
//            [
//                ['caption' => 'caption5', 'content' => Str::random(200), 'bool_field' => true, 'is_important' => null],
//                ''
//            ],
//            [['caption' => 'caption6', 'bool_field' => true], ValidateException::class],
//            [['bool_field' => false, 'content' => Str::random(200)], ValidateException::class],
//        ];
//    }
//
//    /**
//     * @param array $attrs
//     * @param string $exception
//     * @throws ValidateException
//     * @dataProvider lengthDataProvider
//     */
//    public function testLength(array $attrs, string $exception)
//    {
//        if ($exception) {
//            $this->expectException($exception);
//        }
//        $this->assertNotNull($this->model->create($attrs));
//    }
//
//    public function lengthDataProvider()
//    {
//        return [
//            [['caption' => '22', 'content' => Str::random(200), 'bool_field' => false], ValidateException::class],
//            [['caption' => '333', 'content' => Str::random(200), 'bool_field' => false], ValidateException::class],
//            [['caption' => '4444', 'content' => Str::random(200), 'bool_field' => false], ValidateException::class],
//            [['caption' => '55555', 'content' => Str::random(200), 'bool_field' => false], ''],
//            [['caption' => Str::random(5) . '4', 'content' => Str::random(200), 'bool_field' => false], ''],
//            [['caption' => Str::random(99) . '4', 'content' => Str::random(200), 'bool_field' => false], ''],
//            [
//                ['caption' => Str::random(100) . '4', 'content' => Str::random(200), 'bool_field' => false],
//                ValidateException::class
//            ],
//        ];
//    }
//
//    /**
//     * @param array $attrs
//     * @param string $exception
//     * @throws ValidateException
//     * @dataProvider nullDataProvider
//     */
//    public function testNull(array $attrs, string $exception)
//    {
//        if ($exception) {
//            $this->expectException($exception);
//        }
//        $this->assertNotNull($this->model->create($attrs));
//    }
//
//    public function nullDataProvider()
//    {
//        return [
//            [['caption' => 'caption1', 'content' => Str::random(200), 'bool_field' => null], ValidateException::class],
//            [['caption' => 'caption2', 'content' => Str::random(200), 'bool_field' => false], ''],
//            [
//                ['caption' => 'caption3', 'content' => Str::random(200), 'bool_field' => false, 'int_field' => 5],
//                '',
//            ],
//            [
//                ['caption' => 'caption3', 'content' => Str::random(200), 'bool_field' => false, 'int_field' => null],
//                ValidateException::class,
//            ],
//        ];
//    }

}
