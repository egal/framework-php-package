<?php

namespace Egal\Interface\Metadata\Widgets\Select;

use Egal\Interface\Metadata\Configuration;

class SelectDataConfiguration extends Configuration
{
    protected string $requestModel;

    public static function make(): self
    {
        return new self();
    }

    public function getRequestModel(): string
    {
        return $this->requestModel;
    }

    public function setRequestModel(string $requestModel): self
    {
        $this->requestModel = $requestModel;

        return $this;
    }

}
