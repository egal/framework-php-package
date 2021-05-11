<?php

namespace EgalFramework\Model\Exceptions;

class WhereException extends Exception
{

    protected ?string $baseMessageLine = 'Search impossible!';
    protected ?int $defaultCode = 405;

}