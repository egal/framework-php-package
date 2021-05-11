<?php

namespace EgalFramework\Model\Tests;

use EgalFramework\Common\Session;
use EgalFramework\Model\Tests\Samples\Models\Test;
use EgalFramework\Model\Tests\Samples\Stubs\ModelManager;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use PHPUnit\Framework\TestCase;

class HashTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        Session::setModelManager(new ModelManager);

        $this->setUpDB();
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
            $table->boolean('is_important')->default(FALSE)->nullable();
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

    protected function tearDown(): void
    {
        DB::schema()->dropIfExists('tests');
        parent::tearDown();
    }

    public function testHash()
    {
        $item = new Test;
        $item->caption = 'Supercaption!!111';
        $item->content = Str::random(200);
        $item->save();
        $this->assertEquals(
            $item->hash,
            hash(
                'SHA256',
                json_encode([
                    'caption' => $item->caption,
                    'content' => $item->content,
                    'date_field' => $item->date_field,
                    'id' => $item->id,
                    'time_field' => $item->time_field,
                ])
            )
        );
    }

}
