<?php

use Illuminate\Database\Migrations\Migration as BaseMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends BaseMigration {

    public function up()
    {
        $this->down();
        Schema::create('models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('string');
            $table->integer('integer');
            $table->double('numeric');
            $table->boolean('boolean');
            $table->json('array');
            $table->json('json');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('models');
    }

};
