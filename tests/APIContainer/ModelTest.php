<?php

namespace EgalFramework\APIContainer\Tests;

use EgalFramework\APIContainer\Models\Argument;
use EgalFramework\APIContainer\Models\Method;
use EgalFramework\APIContainer\Models\Model;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{

    public function testModel()
    {
        $argument = new Argument;
        $argument->name = 'argName';
        $argument->description = 'argDescription';
        $argument->type = 'int';

        $testArgument = new Argument;
        $testArgument->fromString($argument->toString());
        $this->assertEquals($argument->toString(), $testArgument->toString());

        $method = new Method;
        $method->name = 'methodName';
        $method->description = 'methodDescription';
        $method->summary = 'methodSummary';
        $method->fromClass = 'fromClass';
        $method->return = 'bool';
        $method->roles = ['admin', 'user', 'nightBlood'];
        $method->arguments = [$argument];

        $testMethod = new Method;
        $testMethod->fromString($method->toString());
        $this->assertEquals($method->toString(), $testMethod->toString());

        $model = new Model;
        $model->name = 'modelName';
        $model->description = 'description';
        $model->summary = 'summary';
        $model->setMethod($method->name, $method);

        $testModel = new Model;
        $testModel->fromString($model->toString());
        $this->assertEquals($model->toString(), $testModel->toString());
    }

}
