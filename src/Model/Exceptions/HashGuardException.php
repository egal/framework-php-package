<?php

namespace EgalFramework\Model\Exceptions;

class HashGuardException extends Exception
{

    protected ?string $baseMessageLine = 'Data protection error!';
    protected ?int $defaultCode = 403;

}