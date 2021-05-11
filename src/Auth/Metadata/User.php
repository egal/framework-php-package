<?php

namespace EgalFramework\Auth\Metadata;

use EgalFramework\Common\FieldType;
use EgalFramework\Metadata\Field;
use EgalFramework\Metadata\Metadata;

/**
 * Class User
 * @package App\MetaData
 */
class User extends Metadata
{

    /** @var string */
    protected string $label = 'User';

    /** @var string */
    protected string $table = 'users';

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->data = [
            'id' => (new Field(FieldType::PK, '#'))
                ->setInChangeForm(false)
                ->setInCreateForm(false),
            'email' => (new Field(FieldType::EMAIL, 'E-Mail'))
                ->setUnique(true)
                ->setRequired(true),
            'password' => (new Field(FieldType::PASSWORD, 'Password'))
                ->setRequired(true),
            'name' => (new Field(FieldType::STRING, 'Name'))
                ->setRequired(true),
            'is_confirmed' => (new Field(FieldType::BOOL, 'Confirmed'))
                ->setInChangeForm(false)
                ->setInCreateForm(false),
            'created_at' => (new Field(FieldType::DATETIME, 'Created'))
                ->setInCreateForm(false)
                ->setInChangeForm(false),
            'updated_at' => (new Field(FieldType::DATETIME, 'Modified'))
                ->setInCreateForm(false)
                ->setInChangeForm(false),
        ];
        parent::__construct();
    }

}
