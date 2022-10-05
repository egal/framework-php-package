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

        $metadata = m::mock(
            ModelMetadata::class . '[getPrimaryKey,fieldExist,getValidationRules]',
            [Model::class]
        );
        $metadata->shouldReceive('getPrimaryKey')->andReturn($keyName);
        $metadata->shouldReceive('fieldExist')->with($keyName)->andReturn(true);
        $metadata->shouldReceive('getValidationRules')->with($keyName)->andReturn($validationRules);

        $model = m::mock(Model::class . '[getModelMetadata]');
        $model->shouldReceive('getModelMetadata')->andReturn($metadata);

        if ($expectValidateException) {
            $this->expectException(ValidateException::class);
        }

        PHPUnitUtil::callMethod($model, 'validateKey', $keyValue);
    }

}
