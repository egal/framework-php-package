<?php

namespace App\Metadata\Test;

use EgalFramework\Common\FieldType;
use EgalFramework\Metadata\Field;
use EgalFramework\Metadata\Metadata;

/**
 * Class TestModel
 * @package App\Metadata\Test
 */
class TestModel extends Metadata
{

    /** @var string */
    protected string $label = 'Label';

    /** @var string */
    protected string $table = 'test_models';

    /**
     * TestModel constructor.
     */
    public function __construct()
    {
        $this->data = [
            'id' => (new Field(FieldType::PK, '#'))
                ->setInChangeForm(false)
                ->setInCreateForm(false),
            'created_at' => (new Field(FieldType::DATETIME, 'Created'))
                ->setInChangeForm(false)
                ->setInCreateForm(false),
            'updated_at' => (new Field(FieldType::DATETIME, 'Modified'))
                ->setInChangeForm(false)
                ->setInCreateForm(false),
            'hash' => (new Field(FieldType::STRING, 'hash'))
                ->setRequired(true),
        ];
        parent::__construct();
    }

}
