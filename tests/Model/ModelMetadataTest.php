<?php

namespace Egal\Tests\Model;

use Egal\Core\Application;
use Egal\Model\Exceptions\IncorrectCaseOfPropertyVariableNameException;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\TestCase;

class ModelMetadataTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $app = new Application(
            $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
        );
        $app->withFacades();
        Config::set('app.debug', true);
    }

    public function dataProviderTestGetValidationRules(): array
    {
        return [
            [
                fn() => (new ModelMetadata(ModelMetadataTestFirst::class))->getValidationRules(),
                ['id' => ['integer'], 'foo' => ['string', 'max:10']],
                null,
            ],
            [
                fn() => (new ModelMetadata(ModelMetadataTestFirst::class))->getValidationRules('foo'),
                ['string', 'max:10'],
                null,
            ],
            [
                fn() => (new ModelMetadata(ModelMetadataTestFirst::class))->getValidationRules('id'),
                ['integer'],
                null,
            ],
            [
                fn() => new ModelMetadata(ModelMetadataTestSecond::class),
                null,
                IncorrectCaseOfPropertyVariableNameException::class,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestGetValidationRules
     */
    public function testGetValidationRules($getMetadataFunc, $expectResult, $expectException)
    {
        if ($expectException) {
            $this->expectException($expectException);
        }

        $result = $getMetadataFunc();

        if ($expectResult) {
            $this->assertEquals($expectResult, $result);
        }
    }

}

/**
 * @property $id  {@property-type field} {@validation-rules integer}
 * @property $foo {@property-type field} {@validation-rules string|max:10}
 */
class ModelMetadataTestFirst extends Model
{

}

/**
 * @property $fooBar  {@property-type field}
 */
class ModelMetadataTestSecond extends Model
{

}
