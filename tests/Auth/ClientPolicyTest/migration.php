<?php

use Illuminate\Database\Migrations\Migration as BaseMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends BaseMigration
{

    public function up()
    {
        $this->down();
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }

};
