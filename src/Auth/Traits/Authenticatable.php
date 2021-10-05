<?php

namespace Egal\Auth\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 * @deprecated since v2.0.0
 */
trait Authenticatable
{

    /**
     * Get the name of the unique identifier for the user.
     *
     * @deprecated since v2.0.0
     */
    public function getAuthIdentifierName(): string
    {
        return $this->getKeyName();
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     *
     * @deprecated since v2.0.0
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

}
