<?php

namespace Egal\Tests\Model\ModelBootEventsResultsTest;

use Egal\Tests\Model\ModelActionsSetRelationsTest\Models\Company;
use Egal\Tests\Model\ModelActionsSetRelationsTest\Models\Employee;
use Egal\Tests\Model\ModelBootEventsResultsTest\Models\Maintainer;
use Egal\Tests\Model\ModelBootEventsResultsTest\Models\Technique;
use Egal\Tests\TestCase;
use Egal\Tests\DatabaseMigrations;

class Test extends TestCase
{

    use DatabaseMigrations;

    public function getDir(): string
    {
        return __DIR__;
    }

    public function testBootEventsResult()
    {
        $employee = new Employee();

        $maintainer = new Maintainer();

        $technique = new Technique();

        $company = new Company();

        return [
            $employee->save(),
            $maintainer->save(),
            $technique->save(),
            $company->save(),
        ];
    }

}
