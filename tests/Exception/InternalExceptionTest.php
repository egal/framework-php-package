<?php

namespace Egal\Tests\Exception;

use Egal\Exception\InternalException;
use Egal\Exception\NoInternalCodeSetException;
use PHPUnit\Framework\TestCase;

class InternalExceptionTest extends TestCase
{
    public function dataProviderSetExceptionInternalCode()
    {
        return [
            [ FooInternalException::class,  'a',  false ],
            [ FooInternalException::class,  null,  false ],
            [ BarInternalException::class,  'c',  false ],
            [ BarInternalException::class,  null,  true ],
        ];
    }

    /**
     * @dataProvider dataProviderSetExceptionInternalCode
     */
    public function testExceptions(
        string $constructedException,
        ?string $internalCode,
        bool   $isExpectedException
    )
    {
        if ($isExpectedException) {
            $this->expectException(NoInternalCodeSetException::class);
        }
        new $constructedException('message', 0, $internalCode);
    }

}

class BarInternalException extends InternalException
{

}
class FooInternalException extends InternalException
{
    protected string $internalCode = 'foo';
}
