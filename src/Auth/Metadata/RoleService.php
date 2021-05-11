<?php

namespace EgalFramework\Auth\Metadata;

use EgalFramework\Common\FieldType;
use EgalFramework\Common\RelationType;
use EgalFramework\Metadata\Field;
use EgalFramework\Metadata\Metadata;
use EgalFramework\Metadata\Relation;

class RoleService extends Metadata
{

    /** @var string */
    protected string $label = 'Role to Service';

    /** @var string */
    protected string $table = 'role_service';

    /**
     * RoleUser constructor.
     */
    public function __construct()
    {
        /** @noinspection DuplicatedCode */
        $this->data = [
            'id' => (new Field(FieldType::PK, '#'))
                ->setInChangeForm(false)
                ->setInCreateForm(false),
            'service_id' => (new Field(FieldType::RELATION, 'Service'))
                ->setRelation('Service'),
            'role_id' => (new Field(FieldType::RELATION, 'Role'))
                ->setRelation('Role'),
            'created_at' => (new Field(FieldType::DATETIME, 'Created'))
                ->setInChangeForm(false)
                ->setInCreateForm(false),
            'updated_at' => (new Field(FieldType::DATETIME, 'Modified'))
                ->setInChangeForm(false)
                ->setInCreateForm(false),
        ];
        $this->relations = [
            'Service' => new Relation(RelationType::BELONGS_TO, 'Service'),
            'Role' => new Relation(RelationType::BELONGS_TO, 'Role'),
        ];
        parent::__construct();
    }

}
