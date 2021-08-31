<?php

namespace Egal\Auth\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait Authenticatable
{

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName(): string
    {
        return $this->getKeyName();
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

}
