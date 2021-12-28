<?php

namespace Egal\Tests\Model;

use Egal\Model\EnumModel;
use PHPUnit\Framework\TestCase;

class EnumModelTest extends TestCase
{
    public function testEnumGetItems()
    {
        $expectResult = [
            'INCOMPLETE' => 'incomplete',
            'COMPLETE' => 'complete'
        ];
        $actualResult = EnumModelActionGetItemsTestStatusStub::actionGetItems();

        $this->assertEquals($expectResult, $actualResult);
    }

}

class EnumModelActionGetItemsTestStatusStub extends EnumModel
{
    const INCOMPLETE = 'incomplete';
    const COMPLETE = 'complete';
}
