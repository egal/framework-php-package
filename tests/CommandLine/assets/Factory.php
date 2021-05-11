<?php

/** @var Factory $factory */

use App\PublicModels\Test\TestModel as Model;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Model::class, function (Faker $faker) {
    return [
        'There are factory fields :)',
    ];
});
