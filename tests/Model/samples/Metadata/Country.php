<?php

namespace EgalFramework\Model\Tests\Samples\Metadata;

use EgalFramework\Common\FieldType;
use EgalFramework\Model\Tests\Samples\Stubs\Field;

/**
 * Class Article
 * @package App\Metadata
 */
class Country extends Metadata
{

    protected string $label = 'Test Model';

    protected string $viewName = 'caption';

    protected string $table = 'countries';

    /**
     * Article constructor.
     */
    public function __construct()
    {
        $this->data = [
            'id' => (new Field(FieldType::PK, '#'))->setInCreateForm(false)->setInChangeForm(false),
            'name' => (new Field(FieldType::STRING, 'Заголовок'))
                ->setRequired(true),
            'hash' => (new Field(FieldType::STRING, 'hash'))
                ->setRequired(true)
                ->setHideFromUser(true),
        ];
    }

    public function setSupportFullSearch()
    {
        $this->supportFullSearch = true;
    }

}
