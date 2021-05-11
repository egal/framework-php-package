<?php

use Illuminate\Database\Seeder;
use App\PublicModels\Test\TestModel as Model;

class TestModelSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Model::class, 10)->create();
    }

}
