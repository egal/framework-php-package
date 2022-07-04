<?php

namespace Egal\Core\Rest\Select;

use Egal\Core\Exceptions\SelectParseException;
use Egal\Core\Rest\Filter\Field;
use Egal\Core\Rest\Filter\RelationField;

class Parser
{
    public const FIELDS_DELIMITER = ",";

    public function parse(?string $queryString): array
    {
        $fields = [];

        if ($queryString === '' || $queryString === null) {
            return $fields;
        }

        $fieldsRaw = explode(self::FIELDS_DELIMITER, str_replace(' ','',$queryString));

        foreach ($fieldsRaw as $field) {
            switch (true) {
                // TODO нужен ли тип MorphRelationField?
                case preg_match("/^" . RelationField::REG_PATTERN . "$/", $field, $matches):
                    $fields[] = new RelationField($matches['relation_field'], $matches['relation']);
                    break;
                case preg_match("/^" . Field::REG_PATTERN . "$/", $field):
                    $fields[] = new Field($field);
                    break;
                default:
                    throw new SelectParseException();
            }
        }

        return $fields;
    }
}
