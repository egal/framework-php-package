<?php

namespace Egal\Tests\Model;

use Egal\Core\Exceptions\DomainClassifiedException;
use Egal\Core\Exceptions\NonUniqueDomainCodeException;
use PHPUnit\Framework\TestCase;

class DomainClassifiedExceptionTest extends TestCase
{
    public function dataProviderSetExceptionDomainCode()
    {
        return [
            [ FooDomainClassifiedException::class,  'a',  false ],
            [ FooDomainClassifiedException::class,  'b',  false ],
            [ BarDomainClassifiedException::class,  'c',  false ],
            [ BarDomainClassifiedException::class,  'a',  true ],
        ];
    }

    /**
     * @dataProvider dataProviderSetExceptionDomainCode
     */
    public function testExceptions(
        string $constructedException,
        string $domainCode,
        bool $isExpectedException
    )
    {
        if ($isExpectedException) {
            $this->expectException(NonUniqueDomainCodeException::class);
        }
        new $constructedException($domainCode);
    }

}

class BarDomainClassifiedException extends DomainClassifiedException
{

}
class FooDomainClassifiedException extends DomainClassifiedException
{

}
