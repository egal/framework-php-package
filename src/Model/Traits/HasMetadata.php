<?php

namespace EgalFramework\Model\Traits;

use EgalFramework\Common\Interfaces\MetadataInterface;
use EgalFramework\Common\Session;
use ReflectionClass;
use ReflectionException;

trait HasMetadata
{

    protected function initializeUsesMetadata()
    {
        $this->makeHiddenFieldFromMetadata();
    }

    /**
     * @return MetadataInterface
     * @throws ReflectionException
     */
    public static function getMetadata()
    {
        $class = static::class;
        $classShortName = (new ReflectionClass($class))->getShortName();
        return Session::getMetadata($classShortName);
    }

    /**
     * @return $this
     */
    public function makeHiddenFieldFromMetadata()
    {
        $fields = $this->metadata->getFields();
        $hiddenFieldNamesFromMetadata = [];
        $hiddenFieldFromUserNames = [];
        foreach ($fields as $fieldName => $field) {
            if ($field->getHidden()) {
                $hiddenFieldNamesFromMetadata[] = $fieldName;
            }
            if ($field->getHideFromUser()) {
                $hiddenFieldFromUserNames[] = $fieldName;
            }
        }
        $hiddenFieldNames = array_unique(
            $this->metadata->getHiddenFields()
            + $hiddenFieldNamesFromMetadata
            + $hiddenFieldFromUserNames
        );
        $this->makeHidden($hiddenFieldNames);
        return $this;
    }

}