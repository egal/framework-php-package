<?php

use Egal\Tests\Model\ModelBootEventsResultsTest\Models\Company;
use Egal\Tests\Model\ModelBootEventsResultsTest\Models\Employee;
use Egal\Tests\Model\ModelBootEventsResultsTest\Models\Maintainer;
use Egal\Tests\Model\ModelBootEventsResultsTest\Models\Technique;
use Illuminate\Database\Migrations\Migration as BaseMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends BaseMigration
{

    public function up()
    {
        $this->down();
        Schema::create(Company::TABLE, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
        Schema::create(Employee::TABLE, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
        Schema::create(Maintainer::TABLE, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
        Schema::create(Technique::TABLE, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists(Maintainer::TABLE);
        Schema::dropIfExists(Technique::TABLE);
        Schema::dropIfExists(Employee::TABLE);
        Schema::dropIfExists(Company::TABLE);
    }

};
