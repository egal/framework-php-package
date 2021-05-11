<?php

namespace EgalFramework\Metadata\Tests;

use EgalFramework\Common\FieldType;
use EgalFramework\Metadata\Exception;
use EgalFramework\Metadata\Field;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        putenv('APP_DEBUG=true');
    }

    public function testFieldNoMethodException()
    {
        $field = new Field('string', 'Name');
        $this->expectException(Exception::class);
        /** @noinspection PhpUndefinedMethodInspection */
        $field->unknownMethod();
    }

    public function testFieldSetUnknown()
    {
        $field = new Field('string', 'Name');
        $this->expectException(Exception::class);
        /** @noinspection PhpUndefinedMethodInspection */
        $field->setUnknown();
    }

    public function testTooMuchSet()
    {
        $field = new Field('string', 'Name');
        $this->expectException(Exception::class);
        $field->setInCreateForm(false, 2);
    }

    public function testFieldGetUnknown()
    {
        $field = new Field('string', 'Name');
        $this->expectException(Exception::class);
        /** @noinspection PhpUndefinedMethodInspection */
        $field->getUnknown();
    }

    public function testFieldToArray()
    {
        $field = new Field('string', 'Name');
        $this->assertEquals(['label' => 'Name', 'type' => 'string'], $field->toArray());
    }

    public function testFieldGetList()
    {
        $field = new Field('list', 'Name');
        $field->setList([1 => '2', '3' => 4]);
        $this->assertEquals([['id' => 1, 'name' => '2'], ['id' => '3', 'name' => 4]], $field->getList());
    }

    public function testFieldSkip()
    {
        $field = new Field('string', 'Name');
        $field->setTechnicalDescription('123');
        putenv('APP_DEBUG=false');
        $this->assertEquals(['label' => 'Name', 'type' => 'string'], $field->toArray());
    }

    public function testValidationRules()
    {
        $field = (new Field('string', 'Name'))
            ->setUnique(true)
            ->setValidationRules(['value']);
        $valid = ['string', 'unique:table,name', 'value'];
        $fromField = $field->getAllValidationRules('table', 'name', false);
        sort($valid);
        sort($fromField);
        $this->assertEquals($valid, $fromField);

        $field = new Field(FieldType::DATETIME, 'DT');
        $valid = ['date_format:Y-m-d H:i:s'];
        $fromField = $field->getAllValidationRules('table', 'name', false);
        sort($valid);
        sort($fromField);
        $this->assertEquals($valid, $fromField);

        $field = (new Field(FieldType::LIST))
            ->setList([1 => 'qwe', 2 => 'ewq', 3 => 'xxx']);
        $valid = ['in:1,2,3'];
        $fromField = $field->getAllValidationRules('table', 'name', false);
        sort($valid);
        sort($fromField);
        $this->assertEquals($valid, $fromField);
    }

    public function testMigration()
    {
        $field = (new Field(FieldType::PK, 'id'))
            ->setUnique(true)
            ->setDefaultValue(123)
            ->setTechnicalDescription('comment!!!!11');
        $migration = $field->getMigration('id');
        $valid = '$table->bigIncrements(\'id\')->unique()->comment(\'comment!!!!11\');';
        $this->assertEquals($valid, $migration);

        $field = new Field('qqq', 'zzz');
        $this->assertEquals('', $field->getMigration('zzz'));

        $field = (new Field(FieldType::STRING, 'tst'));
        $this->assertEquals('$table->string(\'tst\')->nullable();', $field->getMigration('tst'));
    }

    public function testListIsNull()
    {
        $field = new Field(FieldType::FAKE);
        $this->assertNull($field->getList());
    }

}
