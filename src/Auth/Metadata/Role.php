<?php

namespace EgalFramework\Auth\Metadata;

use EgalFramework\Common\FieldType;
use EgalFramework\Metadata\Field;
use EgalFramework\Metadata\Metadata;

/**
 * Class Role
 * @package App\MetaData
 */
class Role extends Metadata
{

    protected string $label = 'Role';

    protected string $table = 'roles';

    /**
     * Role constructor.
     */
    public function __construct()
    {
        $this->data = [
            'id' => (new Field(FieldType::PK, '#'))
                ->setInChangeForm(false)
                ->setInCreateForm(false),
            'internal_name' => (new Field(FieldType::STRING, 'Internal name'))
                ->setRequired(true)
                ->setUnique(true),
            'name' => (new Field(FieldType::STRING, 'Name'))
                ->setRequired(true)
                ->setUnique(true),
            'created_at' => new Field(FieldType::DATETIME, 'Created'),
            'updated_at' => new Field(FieldType::DATETIME, 'Modified'),
            'is_default' => new Field(FieldType::BOOL, 'is_default'),
        ];
        parent::__construct();
    }

}
