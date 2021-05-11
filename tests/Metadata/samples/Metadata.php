<?php

namespace EgalFramework\Metadata\Tests\Samples;

use EgalFramework\Common\FieldType;
use EgalFramework\Common\RelationType;
use EgalFramework\Metadata\Metadata as AMetadata;
use EgalFramework\Metadata\Field;
use EgalFramework\Metadata\FilterField;
use EgalFramework\Metadata\Relation;
use EgalFramework\Metadata\RelationDirection;

class Metadata extends AMetadata
{

    protected string $table = 'null';

    protected string $label = 'Label';

    public function __construct()
    {
        $this->data = [
            'id' => (new Field(FieldType::PK, '#'))->setInCreateForm(false)->setInChangeForm(false),
            'caption' => (new Field(FieldType::STRING, 'Заголовок'))
                ->setRegex('^\w[\w\d]+\d$', 'i')
                ->setRequired(true)
                ->setMin(5)
                ->setMax(100),
            'content' => (new Field(FieldType::TEXT, 'Содержание'))
                ->setInList(false)
                ->setRequired(true)
                ->setMin(100),
            'country_id' => (new Field(FieldType::RELATION, 'Страна'))
                ->setRequired(true)
                ->setModel('Country'),
            'is_important' => (new Field(FieldType::BOOL, 'Важное'))
                ->setDefaultValue(true),
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
                ->setDefaultValue(5)
                ->setValidationRules([]),
            'fake_field' => (new Field(FieldType::FAKE, 'Fake Field'))
                ->setRequired(false)
                ->setInList(true)
                ->setInCreateForm(false)
                ->setInChangeForm(false)
                ->setInViewForm(true)
                ->setIsNullable(true),
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
                ->setDefaultValue(false)
                ->setList([
                    true => 'Да',
                    false => 'Нет',
                ])
                ->setRequired(true)
                ->setInList(true),
            'json_field' => new Field(FieldType::JSON, 'JSON Field')
        ];
        $this->filterFields = [
            new FilterField(FilterField::TYPE_RELATION, 'Country'),
            new FilterField(FilterField::TYPE_FIELD, 'list_field'),
        ];
        $this->relations = [
            'Country' => new Relation(RelationType::BELONGS_TO, 'Country'),
        ];

        parent::__construct();
    }

    public function unsetData()
    {
        unset($this->data);
    }

    public function setSupportFullSearch()
    {
        $this->supportFullSearch = true;
    }

    public function addFaultField()
    {
        $this->filterFields[] = new FilterField('qwe', 'ewq');
    }

    public function setTreeRelation()
    {
        $this->treeRelation = $this->relations['Country'];
    }

    public function setTreeDirection()
    {
        $this->treeDirection = new RelationDirection('Model', 123);
    }

}
