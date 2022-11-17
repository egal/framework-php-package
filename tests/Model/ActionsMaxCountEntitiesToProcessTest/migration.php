<?php

use Illuminate\Database\Migrations\Migration as BaseMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends BaseMigration {

    public function up()
    {
        $this->down();
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('orders');
    }

};
