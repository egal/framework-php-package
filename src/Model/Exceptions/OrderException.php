<?php

namespace EgalFramework\Model\Exceptions;

class OrderException extends Exception
{

    protected ?string $baseMessageLine = 'Sorting is not possible!';
    protected ?int $defaultCode = 405;

}