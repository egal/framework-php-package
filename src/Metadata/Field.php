<?php /** @noinspection PhpUnusedPrivateMethodInspection */

/** @noinspection PhpUnusedPrivateFieldInspection */

namespace EgalFramework\Metadata;

use EgalFramework\Common\FieldType;
use EgalFramework\Common\Interfaces\FieldInterface;
use EgalFramework\Common\SortOrder;

/**
 * Class Field
 * @package App\MetaData
 *
 * @method $this setLabel(string $label)
 * @method $this setType(string $type)
 * @method $this setDefaultValue(string $value)
 * @method $this setInList(bool $inList)
 * @method $this setInCreateForm(bool $inCreateForm)
 * @method $this setInChangeForm(bool $inChangeForm)
 * @method $this setInViewForm(bool $inViewForm)
 * @method $this setRenderPolicy(bool $doRender)
 * @method $this setHideFromUser(bool $hideFromUser)
 * @method $this setRelation(string $relationName) Set relation name from $relations array
 * @method $this setHidden(bool $hidden)
 * @method $this setRequired(bool $required)
 * @method $this setList(array $list)
 * @method $this setMax(float $max)
 * @method $this setMin(float $min)
 * @method $this setTechnicalDescription(string $description)
 * @method $this setReadonlyOnCreate(bool $readonly)
 * @method $this setReadonlyOnChange(bool $readonly)
 * @method $this setUnique(bool $unique)
 * @method $this setUserDescription(string $str)
 * @method $this setIsRange(bool $isRange)
 * @method $this setIsNullable(bool $nullable)
 * @method $this setSortable(bool $sortable)
 * @method $this setMultiple(bool $multiple)
 * @method $this setInMassChange(bool $inMassChange)
 * @method $this setValidationRules(array $rules)
 *
 * @method string getLabel()
 * @method string getDefaultValue()
 * @method bool getInList()
 * @method bool getInCreateForm()
 * @method bool getInChangeForm()
 * @method bool getInViewForm()
 * @method bool getRenderPolicy()
 * @method bool getHideFromUser()
 * @method bool getHidden()
 * @method bool getRequired()
 * @method float getMax()
 * @method float getMin()
 * @method string getTechnicalDescription()
 * @method string getRegex()
 * @method bool getUnique()
 * @method string getUserDescription()
 * @method bool getIsRange()
 * @method bool getIsNullable()
 * @method bool getSortable()
 * @method bool getMultiple()
 * @method bool getInMassChange()
 * @method array getValidationRules()
 */
class Field implements FieldInterface
{

    private string $label;

    private string $type;

    /** @var mixed */
    private $defaultValue;

    private ?bool $inList;

    private ?bool $inCreateForm;

    private ?bool $inChangeForm;

    private ?bool $inViewForm;

    private ?bool $renderPolicy;

    private ?bool $hideFromUser;

    private ?string $relation;

    private ?bool $hidden;

    private ?bool $required;

    private ?array $list;

    private ?float $max;

    private ?float $min;

    private ?string $technicalDescription;

    /** @var string[] */
    private ?array $regex;

    private ?bool $readonly;

    private ?bool $readonlyOnCreate;

    private ?bool $readonlyOnChange;

    private ?bool $unique;

    private ?string $userDescription;

    private ?bool $isRange;

    private ?bool $isNullable;

    private ?bool $sortable;

    private string $defaultSortOrder;

    private ?bool $multiple;

    private ?bool $inMassChange;

    private ?array $validationRules;

    /**
     * Field constructor.
     * @param string $type
     * @param string $label
     */
    public function __construct(string $type, string $label = '')
    {
        $this->setType($type);
        $this->setLabel($label);
        $this->required = null;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setReadonly(bool $readonly): self
    {
        $this->readonly = $readonly;
        return $this;
    }

    public function getReadonly(): bool
    {
        return isset($this->readonly) && $this->readonly;
    }

    public function getReadonlyOnCreate(): bool
    {
        return isset($this->readonlyOnCreate) && $this->readonlyOnCreate;
    }

    public function getReadonlyOnChange(): bool
    {
        return isset($this->readonlyOnChange) && $this->readonlyOnChange;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return $this|null|mixed
     * @throws Exception
     */
    public function __call(string $name, array $arguments = [])
    {
        if (preg_match('/^(set|get)(.+)/', $name, $match)) {
            return call_user_func([$this, $match[1]], lcfirst($match[2]), $arguments);
        }
        throw new Exception('Method ' . $name . ' does not exists for ' . static::class, 404);
    }

    /**
     * @param string $field
     * @param array $arguments
     * @return $this
     * @throws Exception
     */
    private function set(string $field, array $arguments)
    {
        if (!property_exists($this, $field)) {
            throw new Exception('Incorrect property ' . $field . ' in ' . static::class . '::set', 400);
        }
        if (count($arguments) > 1) {
            throw new Exception('Incorrect argument count in ' . static::class . '::set', 400);
        }
        $this->{$field} = $arguments[0];
        return $this;
    }

    /**
     * @param string $defaultSortOrder
     * @return Field
     * @throws Exception
     */
    public function setDefaultSortOrder(string $defaultSortOrder): Field
    {
        if (!SortOrder::check($defaultSortOrder)) {
            throw new Exception('Incorrect sort order ' . $defaultSortOrder, 500);
        }
        $this->defaultSortOrder = $defaultSortOrder;
        return $this;
    }

    /**
     * @param string $field
     * @return mixed
     * @throws Exception
     */
    private function get(string $field)
    {
        if (!property_exists($this, $field)) {
            throw new Exception('Incorrect property ' . $field . ' in ' . static::class . '::get', 400);
        }
        return isset($this->{$field})
            ? $this->{$field}
            : null;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [];
        foreach (array_keys(get_object_vars($this)) as $field) {
            if ($this->isFieldToSkip($field)) {
                continue;
            }
            $value = $this->{'get' . ucfirst($field)}();
            if (!isset($value) || is_null($value)) {
                continue;
            }
            $result[$field] = $value;
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getList()
    {
        if (!isset($this->list)) {
            return NULL;
        }
        $result = [];
        foreach ($this->list as $key => $value) {
            $result[] = ['id' => $key, 'name' => $value];
        }
        return $result;
    }

    private function isFieldToSkip(string $field): bool
    {
        if ($field == 'validationRules') {
            return TRUE;
        }
        if ($field == 'technicalDescription' && !env('APP_DEBUG')) {
            return TRUE;
        }
        return FALSE;
    }

    public function getAllValidationRules(
        string $table,
        string $fieldName,
        bool $skipRequired,
        Relation $relation = null
    ): array
    {
        if ($this->getHideFromUser()) {
            return [];
        }
        $rules = (isset($this->validationRules) && is_array($this->validationRules))
            ? $this->validationRules
            : [];
        $rules[] = $this->getTypeValidationRule();
        $rules[] = $this->getRelationValidationRule($relation);
        if (!$skipRequired && !empty($this->required)) {
            $rules[] = 'required';
        }
        $rules[] = $this->getListValidationRule();
        if (isset($this->max)) {
            $rules[] = 'max:' . $this->getMax();
        }
        if (isset($this->min)) {
            $rules[] = 'min:' . $this->getMin();
        }
        if (!empty($this->regex['regex'])) {
            $rules[] = 'regex:{' . $this->regex['regex'] . '}' . $this->regex['flags'];
        }
        if (isset($this->unique) && $this->unique) {
            $rules[] = 'unique:' . $table . ',' . $fieldName;
        }
        $rules[] = (isset($this->isNullable) && $this->isNullable)
            ? 'nullable'
            : function ($attribute, $value, $fail) {
                if (is_null($value)) {
                    $fail(sprintf('Field %s can\'t be null', $attribute));
                }
            };
        return array_values(array_filter($rules));
    }

    /**
     * Validation rules by type
     * @return string|null
     */
    private function getTypeValidationRule()
    {
        if ($this->getReadonly()) {
            return NULL;
        }
        switch ($this->type) {
            case FieldType::IMAGE:
            case FieldType::FILE:
            case FieldType::STRING:
            case FieldType::TEXT:
                return FieldType::STRING;
            case FieldType::BOOL:
                return 'boolean';
            case FieldType::DATETIME:
                return 'date_format:Y-m-d H:i:s';
            case FieldType::DATE:
                return 'date_format:Y-m-d';
            case FieldType::TIME:
                return 'date_format:H:i:s';
            case FieldType::EMAIL:
                return 'email:rfc,dns';
            case FieldType::INT:
                return 'numeric';
            default:
                return NULL;
        }
    }

    private function getRelationValidationRule(Relation $relation = null): ?string
    {
        if (
            is_null($relation)
            || $this->type != FieldType::RELATION
            || !empty($relation->getIntermediateModel())
        ) {
            return NULL;
        }
        return 'exists:' . $relation->getRelationTable() . ',id';
    }

    private function getListValidationRule()
    {
        if ($this->type != FieldType::LIST) {
            return NULL;
        }
        return 'in:' . implode(',', array_keys($this->list));
    }

    /**
     * @param string $regex
     * @param string $flags
     * @return $this
     */
    public function setRegex(string $regex, string $flags = '')
    {
        $this->regex = ['regex' => $regex, 'flags' => $flags];
        return $this;
    }

    public function getMigration(string $name): string
    {
        $migrationTypes = [
            FieldType::PK => 'bigIncrements',
            FieldType::STRING => 'string',
            FieldType::TEXT => 'longText',
            FieldType::INT => 'bigInteger',
            FieldType::RELATION => 'bigInteger',
            FieldType::LIST => 'integer',
            FieldType::BOOL => 'boolean',
            FieldType::DATE => 'date',
            FieldType::TIME => 'time',
            FieldType::EMAIL => 'string',
            FieldType::PASSWORD => 'string',
            FieldType::FLOAT => 'float',
            FieldType::DATETIME => 'timestamp',
            FieldType::IMAGE => 'string',
            FieldType::FILE => 'string',
            FieldType::JSON => 'json',
        ];
        if (!isset($migrationTypes[$this->type])) {
            return '';
        }
        return implode('->', array_filter([
                $this->getMigrationField($name, $migrationTypes[$this->type]),
                $this->getMigrationDefault(),
                $this->getMigrationUnique(),
                $this->getMigrationComment(),
            ])) . ';';
    }

    private function getMigrationField(string $name, string $type): string
    {
        return sprintf('$table->%s(\'%s\')', $type, $name);
    }

    private function getMigrationDefault(): string
    {
        if ($this->required || $this->type == FieldType::PK) {
            return '';
        }
        if (is_null($this->defaultValue)) {
            return (in_array($this->type, [FieldType::DATE, FieldType::TIME, FieldType::DATETIME]))
                ? 'default(DB::raw(\'CURRENT_TIMESTAMP\'))'
                : 'nullable()';
        } else {
            return ($this->getType() == FieldType::BOOL)
                ? sprintf('default(%s)', $this->defaultValue
                    ? 'true'
                    : 'false')
                : sprintf('default(\'%s\')', addslashes($this->defaultValue));
        }
    }

    private function getMigrationUnique(): string
    {
        return (isset($this->unique) && $this->unique)
            ? 'unique()'
            : '';
    }

    private function getMigrationComment(): string
    {
        return (isset($this->technicalDescription) && !empty($this->technicalDescription))
            ? sprintf('comment(\'%s\')', addslashes($this->technicalDescription))
            : '';
    }

    public function getRelation(): ?string
    {
        return isset($this->relation)
            ? $this->relation
            : null;
    }

    public function getDefaultSortOrder(): string
    {
        return isset($this->defaultSortOrder)
            ? $this->defaultSortOrder
            : '';
    }

}
