<?php

namespace EgalFramework\Model\Exceptions;

class NotFoundException extends Exception
{

    protected ?string $baseMessageLine = 'Item not found!';
    protected ?int $defaultCode = 404;

}