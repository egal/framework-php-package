<?php

namespace EgalFramework\Metadata\Tests;

use EgalFramework\Common\FieldType;
use EgalFramework\Common\RelationType;
use EgalFramework\Metadata\Exception;
use EgalFramework\Metadata\Field;
use EgalFramework\Metadata\Relation;
use EgalFramework\Metadata\Tests\Samples\Metadata;
use PHPUnit\Framework\TestCase;

class MetadataTest extends TestCase
{

    /** @var Metadata */
    private Metadata $metadata;

    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/samples/Metadata.php';
        $this->metadata = new Samples\Metadata();
    }

    public function testValidationRules()
    {
        $arr = [
            'caption' => [
                'string',
                'regex:{^\w[\w\d]+\d$}i',
                'min:5',
                'max:100',
                'required',
            ],
            'content' => [
                'string',
                'required',
                'min:100',
            ],
            'country_id' => [
                'required',
                'exists:countries,id',
            ],
            'is_important' => [
                'boolean',
            ],
            'date_field' => [
                'date_format:Y-m-d',
            ],
            'time_field' => [
                'date_format:H:i:s',
            ],
            'email' => [
                'email:rfc,dns',
            ],
            'int_field' => [
                'numeric',
            ],
            'list_field' => [
                'in:1,2,3',
            ],
            'float_field' => [
                'max:999999',
                'min:0.01',
            ],
            'bool_field' => [
                'boolean',
                'required',
            ],
            'fake_field' => [
                'nullable',
            ],
        ];
        $this->assertEquals($this->arraySort($arr), $this->arraySort($this->metadata->getValidationRules(false)));
    }

    private function arraySort($array)
    {
        ksort($array);
        foreach ($array as $key => $value) {
            sort($value);
            $array[$key] = array_values($value);
        }
        return $array;
    }

    public function testFake()
    {
        $this->assertEquals(false, $this->metadata->isFake('id'));
        $this->assertEquals(true, $this->metadata->isFake('fake_field'));
    }

    public function testSupportFullSearch()
    {
        $this->assertEquals(false, $this->metadata->getSupportFullSearch());
        $this->metadata->setSupportFullSearch();
        $this->assertEquals(true, $this->metadata->getSupportFullSearch());
    }

    /**
     * @throws Exception
     */
    public function testGetData()
    {
        $metadata = clone $this->metadata;
        $metadata->setTreeRelation();
        $this->assertEquals([
            'label' => 'Label',
            'viewName' => 'name',
            'supportFullSearch' => false,
            'openByRelation' => false,
            'fields' => [
                'id' => [
                    'type' => FieldType::PK,
                    'label' => '#',
                    'inCreateForm' => false,
                    'inChangeForm' => false,
                ],
                'caption' => [
                    'type' => FieldType::STRING,
                    'label' => 'Заголовок',
                    'regex' => ['regex' => '^\w[\w\d]+\d$', 'flags' => 'i'],
                    'required' => true,
                    'min' => 5,
                    'max' => 100,
                ],
                'content' => [
                    'type' => FieldType::TEXT,
                    'label' => 'Содержание',
                    'inList' => false,
                    'required' => true,
                    'min' => 100,
                ],
                'country_id' => [
                    'type' => FieldType::RELATION,
                    'label' => 'Страна',
                    'required' => true,
                    'model' => 'Country',
                ],
                'is_important' => [
                    'type' => FieldType::BOOL,
                    'label' => 'Важное',
                    'defaultValue' => true,
                ],
                'created_at' => [
                    'type' => FieldType::DATETIME,
                    'label' => 'Создано',
                    'inList' => true,
                    'inViewForm' => true,
                    'inChangeForm' => false,
                    'inCreateForm' => false,
                    'readonly' => true,
                ],
                'updated_at' => [
                    'type' => FieldType::DATETIME,
                    'label' => 'Обновлено',
                    'inList' => false,
                    'inViewForm' => true,
                    'inChangeForm' => false,
                    'inCreateForm' => false,
                    'readonly' => true,
                ],
                'date_field' => ['type' => FieldType::DATE, 'label' => 'Date Field'],
                'time_field' => ['type' => FieldType::TIME, 'label' => 'Time Field'],
                'email' => ['type' => FieldType::EMAIL, 'label' => 'E-Mail'],
                'password' => ['type' => FieldType::PASSWORD, 'label' => 'Пароль', 'defaultValue' => 'super secret'],
                'int_field' => ['type' => FieldType::INT, 'label' => 'Int Field', 'defaultValue' => 5],
                'fake_field' => [
                    'type' => FieldType::FAKE,
                    'label' => 'Fake Field',
                    'required' => false,
                    'inList' => true,
                    'inCreateForm' => false,
                    'inChangeForm' => false,
                    'inViewForm' => true,
                    'isNullable' => true,
                ],
                'list_field' => [
                    'type' => FieldType::LIST,
                    'label' => 'List Field',
                    'list' =>
                        [
                            ['id' => 1, 'name' => 'Value 1'],
                            ['id' => 2, 'name' => 'Value 2'],
                            ['id' => 3, 'name' => 'Value 3'],
                        ],
                    'required' => false,
                    'inList' => false,
                ],
                'float_field' => [
                    'type' => FieldType::FLOAT,
                    'label' => 'Float Field',
                    'inList' => true,
                    'max' => 999999,
                    'min' => 0.01,
                    'required' => false,
                ],
                'bool_field' => [
                    'type' => FieldType::BOOL,
                    'label' => 'Bool Field',
                    'technicalDescription' =>
                        'List can be skipped, if skipped - it is a checkbox, if not - it is radio, otherwise use list field',
                    'list' => [
                        ['id' => true, 'name' => 'Да'],
                        ['id' => false, 'name' => 'Нет'],
                    ],
                    'required' => true,
                    'inList' => true,
                    'defaultValue' => false,
                ],
                'json_field' => [
                    'type' => FieldType::JSON,
                    'label' => 'JSON Field',
                ],
            ],
            'relations' => [
                'Country' => ['type' => RelationType::BELONGS_TO, 'relationModel' => 'Country'],
            ],
            'filterFields' => [
                ['type' => 'relation', 'model' => 'Country'],
                ['type' => 'field', 'field' => 'list_field'],
            ],
            'treeRelation' => ['type' => RelationType::BELONGS_TO, 'relationModel' => 'Country'],
        ], $metadata->getData());
        $this->assertEquals(new Relation(RelationType::BELONGS_TO, 'Country'), $metadata->getTreeRelation());
        $this->assertNull($metadata->getField('NoField'));
        $this->assertIsArray($metadata->getFields());

        $this->expectException(Exception::class);
        $this->metadata->unsetData();
        $this->metadata->getData();
    }

    /**
     * @throws Exception
     */
    public function testGetMigration()
    {
        $this->assertEquals(
            [
                '$table->bigIncrements(\'id\');',
                '$table->string(\'caption\');',
                '$table->longText(\'content\');',
                '$table->bigInteger(\'country_id\');',
                '$table->boolean(\'is_important\')->default(true);',
                '$table->timestamp(\'created_at\')->default(DB::raw(\'CURRENT_TIMESTAMP\'));',
                '$table->timestamp(\'updated_at\')->default(DB::raw(\'CURRENT_TIMESTAMP\'));',
                '$table->date(\'date_field\')->default(DB::raw(\'CURRENT_TIMESTAMP\'));',
                '$table->time(\'time_field\')->default(DB::raw(\'CURRENT_TIMESTAMP\'));',
                '$table->string(\'email\')->nullable();',
                '$table->string(\'password\')->default(\'super secret\');',
                '$table->bigInteger(\'int_field\')->default(\'5\');',
                '$table->integer(\'list_field\')->nullable();',
                '$table->float(\'float_field\')->nullable();',
                '$table->boolean(\'bool_field\')->comment(\'List can be skipped, if skipped - it is a checkbox, if not - it is radio, otherwise use list field\');',
                '$table->json(\'json_field\')->nullable();',
            ],
            array_values($this->metadata->getMigration())
        );
    }

    public function testGetNames()
    {
        $names = [
            'id',
            'caption',
            'content',
            'country_id',
            'is_important',
            'created_at',
            'updated_at',
            'date_field',
            'time_field',
            'email',
            'password',
            'int_field',
            'fake_field',
            'list_field',
            'float_field',
            'bool_field',
            'json_field',
        ];
        $this->assertEquals($names, $this->metadata->getFieldNames(false));
        unset($names[array_search('fake_field', $names)]);
        $this->assertEquals(array_values($names), $this->metadata->getFieldNames(true));
    }

    public function testGetters()
    {
        $this->assertEquals('name', $this->metadata->getViewName());
        $this->assertNull($this->metadata->getTreeRelation());
        $this->assertEquals(
            (new Field(FieldType::PK, '#'))->setInCreateForm(false)->setInChangeForm(false),
            $this->metadata->getField('id')
        );
    }

    public function testFaultFilterField()
    {
        $this->expectException(Exception::class);
        $this->metadata->addFaultField();
    }

    public function testGetTreeDirection()
    {
        $this->assertNull($this->metadata->getTreeDirection());
        $this->metadata->setTreeDirection();
        $this->assertEquals(['model' => 'Model', 'id' => 123], $this->metadata->getTreeDirection()->toArray());
    }

}
