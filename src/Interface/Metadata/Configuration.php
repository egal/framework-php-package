<?php

namespace Egal\Interface\Metadata;

abstract class Configuration implements ConfigurationInterface
{
    public function toArray() : array
    {
        $result = [];
        foreach (array_keys(get_object_vars($this)) as $field) {
            $value = $this->{'get' . ucfirst($field)}();
            if (is_null($value)) {
                continue;
            }

            $result[$field] = $value;
        }

        return $result;
    }

}
