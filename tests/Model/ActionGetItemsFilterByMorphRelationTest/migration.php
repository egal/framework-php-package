<?php

use Illuminate\Database\Migrations\Migration as BaseMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends BaseMigration {

    public function up()
    {
        $this->down();
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('sale')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });
        Schema::create('comments', function (Blueprint $table) {
            $table->increments('id');
            $table->morphs('commentable');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('products');
        Schema::dropIfExists('orders');
    }

};
