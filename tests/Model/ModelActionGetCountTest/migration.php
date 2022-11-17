<?php

use Egal\Tests\Model\BootEventsResultsTest\Models\Company;
use Egal\Tests\Model\BootEventsResultsTest\Models\Employee;
use Egal\Tests\Model\BootEventsResultsTest\Models\Maintainer;
use Egal\Tests\Model\BootEventsResultsTest\Models\Technique;
use Illuminate\Database\Migrations\Migration as BaseMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends BaseMigration
{

    public function up()
    {
        $this->down();
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }

};
