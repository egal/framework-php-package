<?php

use Egal\Tests\Model\ActionsWithSetRelationsTest\Models\Company;
use Egal\Tests\Model\ActionsWithSetRelationsTest\Models\CompanyEmployees;
use Egal\Tests\Model\ActionsWithSetRelationsTest\Models\Employee;
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
        });
        Schema::create(Employee::TABLE, function (Blueprint $table) {
            $table->increments('id');
        });
        Schema::create(CompanyEmployees::TABLE, function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('company_id');
            $table->foreignId('employee_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists(CompanyEmployees::TABLE);
        Schema::dropIfExists(Employee::TABLE);
        Schema::dropIfExists(Company::TABLE);
    }

};
