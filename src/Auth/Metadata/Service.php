<?php

namespace EgalFramework\Auth\Metadata;

use EgalFramework\Common\FieldType;
use EgalFramework\Metadata\Field;
use EgalFramework\Metadata\Metadata;

/**
 * Class User
 * @package App\MetaData
 */
class Service extends Metadata
{

    /** @var string */
    protected string $label = 'Service';

    /** @var string */
    protected string $table = 'services';

    /**
     * Service constructor.
     */
    public function __construct()
    {
        $this->data = [
            'id' => (new Field(FieldType::PK, '#'))
                ->setInChangeForm(false)
                ->setInCreateForm(false),
            'name' => (new Field(FieldType::STRING, 'Name'))
                ->setRequired(true),
            'password' => (new Field(FieldType::PASSWORD, 'Password'))
                ->setRequired(true),
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
