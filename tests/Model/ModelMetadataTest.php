<?php

namespace Egal\Tests\Model;

use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use PHPUnit\Framework\TestCase;

class ModelMetadataTest extends TestCase
{

    public function testGetValidationRules()
    {
        $this->assertEquals(
            [
                'id' => ['integer'],
                'foo' => ['string', 'max:10']
            ],
            (new ModelMetadata(ModelMetadataTestModelWithValidationRulesStub::class))
                ->getValidationRules()
        );
    }

    public function testGetValidationRulesOfField()
    {
        $this->assertEquals(
            ['integer'],
            (new ModelMetadata(ModelMetadataTestModelWithValidationRulesStub::class))
                ->getValidationRules('id')
        );
        $this->assertEquals(
            ['string', 'max:10'],
            (new ModelMetadata(ModelMetadataTestModelWithValidationRulesStub::class))
                ->getValidationRules('foo')
        );
    }

}

/**
 * @property $id  {@property-type field} {@validation-rules integer}
 * @property $foo {@property-type field} {@validation-rules string|max:10}
 */
class ModelMetadataTestModelWithValidationRulesStub extends Model
{

}
