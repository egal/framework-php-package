<?php

namespace EgalFramework\Auth\Metadata;

use EgalFramework\Common\FieldType;
use EgalFramework\Common\RelationType;
use EgalFramework\Metadata\Field;
use EgalFramework\Metadata\Metadata;
use EgalFramework\Metadata\Relation;

class RoleUser extends Metadata
{

    /** @var string */
    protected string $label = 'Role to User';

    /** @var string */
    protected string $table = 'role_user';

    /**
     * RoleUser constructor.
     */
    public function __construct()
    {
        $this->data = [
            'id' => (new Field(FieldType::PK, '#'))
                ->setInChangeForm(false)
                ->setInCreateForm(false),
            'user_id' => (new Field(FieldType::RELATION, 'User'))
                ->setRelation('User'),
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
            'User' => new Relation(RelationType::BELONGS_TO, 'User'),
            'Role' => new Relation(RelationType::BELONGS_TO, 'Role'),
        ];
        parent::__construct();
    }

}
