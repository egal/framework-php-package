<?php

namespace Egal\Tests\Model\ModelActionsSetRelationsTest;

use Egal\Auth\Entities\Client;
use Egal\Core\Session\Session;
use Egal\Tests\Model\ModelActionsSetRelationsTest\Models\Company;
use Egal\Tests\Model\ModelActionsSetRelationsTest\Models\CompanyEmployees;
use Egal\Tests\Model\ModelActionsSetRelationsTest\Models\Employee;
use Egal\Tests\PHPUnitUtil;
use Egal\Tests\TestCase;
use Egal\Tests\DatabaseMigrations;
use Mockery as m;

class Test extends TestCase
{

    use DatabaseMigrations;

    public function getDir(): string
    {
        return __DIR__;
    }

    public function testSaveRelation()
    {
        $firstEmployee = new Employee();
        $firstEmployee->save();

        $secondEmployee = new Employee();
        $secondEmployee->save();

        $company = new Company();
        $company->save();

        $company->saveRelation('employees', [$firstEmployee->getKey(), $secondEmployee->getKey()]);

        $this->assertEquals(
            2,
            CompanyEmployees::query()
                ->where('company_id', $company->getKey())
                ->whereIn('employee_id', [$firstEmployee->getKey(), $secondEmployee->getKey()])
                ->count()
        );
    }

    public function testSaveRelationWithActionCreate()
    {
        $firstEmployee = new Employee();
        $firstEmployee->save();

        $secondEmployee = new Employee();
        $secondEmployee->save();

        $user = m::mock(Client::class);
        $user->shouldReceive('mayOrFail')->andReturn(true);

        PHPUnitUtil::setProperty(app(Session::class), 'authEntity', $user);

        Company::actionCreate([], [
            'employees' => [$firstEmployee->getKey(), $secondEmployee->getKey()]
        ]);

        $this->assertEquals(
            2,
            CompanyEmployees::query()
                ->where('company_id', Company::query()->first()->getKey())
                ->whereIn('employee_id', [$firstEmployee->getKey(), $secondEmployee->getKey()])
                ->count()
        );
    }

    public function testSaveRelationWithActionUpdate()
    {
        $firstEmployee = new Employee();
        $firstEmployee->save();

        $secondEmployee = new Employee();
        $secondEmployee->save();

        $company = new Company();
        $company->save();

        $user = m::mock(Client::class);
        $user->shouldReceive('mayOrFail')->andReturn(true);

        PHPUnitUtil::setProperty(app(Session::class), 'authEntity', $user);

        Company::actionUpdate($company->getKey(), [], [
            'employees' => [$firstEmployee->getKey(), $secondEmployee->getKey()]
        ]);

        $this->assertEquals(
            2,
            CompanyEmployees::query()
                ->where('company_id', Company::query()->first()->getKey())
                ->whereIn('employee_id', [$firstEmployee->getKey(), $secondEmployee->getKey()])
                ->count()
        );
    }

}
