<?php

use Illuminate\Database\Migrations\Migration as BaseMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends BaseMigration {

    public function up()
    {
        $this->down();
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
        });

        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('category_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }

};
