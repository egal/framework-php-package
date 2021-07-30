<?php

namespace Egal\Tests\Model;

use Egal\Model\Exceptions\ValidateException;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Egal\Tests\PHPUnitUtil;
use Laravel\Lumen\Application;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ModelUsesValidatorTest extends TestCase
{

    public function testValidateKeyDataProvider()
    {
        return [
            [['integer'], 1, false],
            [['integer'], 'foo', true],
            [['string'], 1, true],
            [['string'], 'foo', false],
            [['string', 'required'], 'foo', false],
        ];
    }

    /**
     * @dataProvider testValidateKeyDataProvider()
     */
    public function testValidateKey(array $validationRules, $keyValue, bool $expectValidateException)
    {
        $app = new Application(dirname(__DIR__));
        $app->withFacades();

        $keyName = 'foo';

        $modelMetadata = m::mock(ModelMetadata::class . '[getValidationRules]', [Model::class]);
        $modelMetadata->shouldReceive('getValidationRules')->with($keyName)->andReturn($validationRules);

        $model = m::mock(Model::class . '[getModelMetadata,getKeyName]');
        $model->shouldReceive('getKeyName')->andReturn($keyName);
        $model->shouldReceive('getModelMetadata')->andReturn($modelMetadata);

        if ($expectValidateException) {
            $this->expectException(ValidateException::class);
        }

        PHPUnitUtil::callMethod($model, 'validateKey', $keyValue);
    }

}
