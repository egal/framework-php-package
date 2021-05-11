<?php

namespace EgalFramework\Common\Interfaces\APIContainer;

interface ParserInterface
{

    public function extract(string $modelName, bool $fullAccess): ModelInterface;

}
