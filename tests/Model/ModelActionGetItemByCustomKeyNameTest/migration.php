<?php

use Egal\Tests\Model\ModelActionGetItemByCustomKeyNameTest\Models\Product;
use Illuminate\Database\Migrations\Migration as BaseMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends BaseMigration
{

    public function up()
    {
        $this->down();
        Schema::create(Product::TABLE,  function (Blueprint $table) {
            $table->string('key');
            $table->string('value');
        });
    }

    public function down()
    {
        Schema::dropIfExists(Product::TABLE);
    }

};
