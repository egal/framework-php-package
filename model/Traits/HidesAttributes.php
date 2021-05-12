<?php

namespace Egal\Model\Traits;

trait HidesAttributes
{

    /**
     * @var string[]
     */
    protected $hidden = [
        'pivot',
        'laravel_through_key',
    ];

}
