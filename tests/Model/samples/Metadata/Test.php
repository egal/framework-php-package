<?php

namespace EgalFramework\Model\Tests\Samples\Metadata;

use EgalFramework\Common\FieldType;
use EgalFramework\Common\Interfaces\RelationInterface;
use EgalFramework\Common\RelationType;
use EgalFramework\Model\Tests\Samples\Stubs\Field;
use EgalFramework\Model\Tests\Samples\Stubs\Relation;

/**
 * Class Article
 * @package App\Metadata
 */
class Test extends Metadata
{

    protected string $label = 'Test Model';

    protected string $viewName = 'caption';

    protected string $table = 'tests';

    /**
     * Article constructor.
     */
    public function __construct()
    {
        $this->data = [
            'id' => (new Field(FieldType::PK, '#'))->setInCreateForm(false)->setInChangeForm(false),
            'caption' => (new Field(FieldType::STRING, 'Заголовок'))
                ->setRegex('^\w[\w\d]+\d$', 'i')
                ->setRequired(true)
                ->setMin(5)
                ->setMax(100)
                ->setUserDescription(
                    'Название должно содержать минимум три символа, начинаться с буквы, заканчиваться цифрой и '
                    . 'содержать не менее трёх букв'
                )
                ->setInFilter(true),
            'content' => (new Field(FieldType::TEXT, 'Содержание'))
                ->setInList(false)
                ->setRequired(true)
                ->setMin(100),
            'country_id' => (new Field(FieldType::RELATION, 'Страна'))
                ->setModel('Country')
                ->setRequired(false)
                ->setInFilter(true),
            'is_important' => (new Field(FieldType::BOOL, 'Важное'))
                ->setInFilter(true)
                ->setIsNullable(true),
            'created_at' => (new Field(FieldType::DATETIME, 'Создано'))
                ->setInList(true)
                ->setInViewForm(true)
                ->setInChangeForm(false)
                ->setInCreateForm(false)
                ->setReadonly(true),
            'updated_at' => (new Field(FieldType::DATETIME, 'Обновлено'))
                ->setInList(false)
                ->setInViewForm(true)
                ->setInChangeForm(false)
                ->setInCreateForm(false)
                ->setReadonly(true),
            'date_field' => new Field(FieldType::DATE, 'Date Field'),
            'time_field' => new Field(FieldType::TIME, 'Time Field'),
            'email' => new Field(FieldType::EMAIL, 'E-Mail'),
            'password' => (new Field(FieldType::PASSWORD, 'Пароль'))
                ->setDefaultValue('super secret'),
            'int_field' => (new Field(FieldType::INT, 'Int Field'))
                ->setDefaultValue(5),
            'fake_field' => (new Field(FieldType::FAKE, 'Fake Field'))
                ->setRequired(false)
                ->setInList(true)
                ->setInCreateForm(false)
                ->setInChangeForm(false)
                ->setInViewForm(true),
            'list_field' => (new Field(FieldType::LIST, 'List Field'))
                ->setList([
                    1 => 'Value 1',
                    2 => 'Value 2',
                    3 => 'Value 3',
                ])
                ->setRequired(false)
                ->setInList(false),
            'float_field' => (new Field(FieldType::FLOAT, 'Float Field'))
                ->setInList(true)
                ->setMax(999999)
                ->setMin(0.01)
                ->setRequired(false),
            'bool_field' => (new Field(FieldType::BOOL, 'Bool Field'))
                ->setTechnicalDescription(
                    'List can be skipped, if skipped - it is a checkbox, if not - it is radio, otherwise use list field'
                )
                ->setList([
                    true => 'Да',
                    false => 'Нет',
                ])
                ->setRequired(true)
                ->setInList(true),
            'hash' => (new Field(FieldType::STRING, 'hash'))
                ->setRequired(true)
                ->setHideFromUser(true),
        ];
        $this->relations = [
            'Country' => new Relation(RelationType::BELONGS_TO, 'Country'),
        ];
    }

    public function setSupportFullSearch()
    {
        $this->supportFullSearch = true;
    }

    public function getTreeRelation(): RelationInterface
    {
        return new Relation(RelationType::ONE_TO_ONE, 'Country');
    }

}
