<?php

use Egal\Tests\Model\ActionGetItemByCustomKeyNameTest\Models\Product;
use Illuminate\Database\Migrations\Migration as BaseMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends BaseMigration
{

    public function up(): void
    {
        $this->down();
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('category_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }

};
