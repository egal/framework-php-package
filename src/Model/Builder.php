<?php

namespace EgalFramework\Model;

use EgalFramework\Common\Interfaces\MetadataInterface;
use EgalFramework\Common\SortOrder;
use EgalFramework\Model\Deprecated\Traits\Model\Relation;
use EgalFramework\Model\Exceptions\NotFoundException;
use EgalFramework\Model\Exceptions\OrderException;
use EgalFramework\Model\Exceptions\WhereException;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation as IlluminateRelation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionException;
use ReflectionMethod;

class Builder extends EloquentBuilder
{

    private MetadataInterface $metadata;
    protected int $countWhereHas = 0;

    /**
     * Получите экземпляр отношения для данного имени отношения.
     *
     * Метод {@see EloquentBuilder::getRelation()} дополненный более точной проверкой наличия запрашиваемого отношения.
     * Список дополнительных проверок:
     * проверка на наличие метода,
     * должно быть указано возвращаемое значение на уровне PHP,
     * тип возвращаемого значения должен быть потомком {@see Relation}
     *
     * @param string $name Название отношения
     * @return IlluminateRelation
     * @throws ReflectionException
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection PhpMissingParamTypeInspection
     */
    public function getRelation($name)
    {
        $name = Str::camel($name);

        $model = $this->getModel();
        $modelClass = get_class($model);
        if (!method_exists($modelClass, $name)) {
            throw RelationNotFoundException::make($model, $name);
        }
        $returnType = (new ReflectionMethod($modelClass, $name))->getReturnType();
        if (is_null($returnType)) {
            throw RelationNotFoundException::make($model, $name);
        }
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $returnTypeName = $returnType->getName();
        if (!class_exists($returnTypeName) || !is_a($returnTypeName, IlluminateRelation::class, true)) {
            throw RelationNotFoundException::make($model, $name);
        }

        return parent::getRelation($name);
    }

    /**
     * Сортировка по определенному field с порядком сортировки
     *
     * domain/Service/Entity/getItems?_order={"field": "asc"|"desc"}
     * Сортирует все сущности Entity по field с порядком сортировки asc|desc
     *
     * @param array $order
     * @return $this
     * @throws OrderException
     */
    public function setOrder(array $order)
    {
        if (empty($order)) {
            $order = $this->metadata->getDefaultSortBy();
        }

        if (empty($order)) {
            return $this;
        }

        foreach ($order as $fieldName => $sortOrder) {
            $field = $this->metadata->getField($fieldName);
            if (is_null($field)) {
                throw new OrderException("The $fieldName field does not exist!");
            }
            if (!SortOrder::check(strtolower($sortOrder))) {
                throw new OrderException("Unsupported mapping type $sortOrder!");
            }
            $this->orderBy($fieldName, $sortOrder);
        }
        return $this;
    }

    /**
     * Неточный поиск по определенным field
     *
     * domain/Service/Entity/getItems?_search={"field":"value"}
     * Находит все Entity где field ILIKE %value%
     *
     * @param array $substringSearchValues
     * @return $this
     */
    public function setSubstringSearchValues(array $substringSearchValues)
    {
        foreach ($substringSearchValues as $filed => $value) {
            $this->setSubstringSearchValue($filed, $value);
        }
        return $this;
    }

    /**
     * Неточный поиск по определенному field
     *
     * @param string $substringSearchField
     * @param mixed $substringSearchValue
     * @return $this
     */
    public function setSubstringSearchValue(string $substringSearchField, $substringSearchValue)
    {
        $this->where($substringSearchField, 'ILIKE', '%' . $substringSearchValue . '%');
        return $this;
    }

    /**
     * Точный поиск по определенным fields и relations модели
     *
     * domain/Service/Entity/getItems?field_name=field_value
     * Ищет все Entity где field_name === field_value
     * Или
     * domain/Service/Entity/getItems?relation_name={"relation_field_name": "relation_field_value"}
     * все Entity у которых в relation_name какой-то relation_field_name === relation_field_value
     *
     * @param array $fields
     * @return $this
     * @throws WhereException
     */
    public function setWhereValues(array $fields)
    {
        $notFakeFields = $this->metadata->getFieldNames(true);
        foreach ($fields as $fieldName => $fieldValue) {
            if (in_array($fieldName, $notFakeFields)) {
                $this->setWhereValue($fieldName, $fieldValue);
            } else {
                $relationName = Str::camel($fieldName);
                if (is_array($fieldValue)) {
                    $this->where(function ($query) use ($fieldValue, $relationName) {
                        foreach ($fieldValue as $relationAttributes) {
                            foreach ($relationAttributes as $relationAttributeName => $relationAttributeValue) {
                                $query->setWhereHasValue($relationName, $relationAttributeName, $relationAttributeValue);
                            }
                        }
                    });
                } elseif (is_string($fieldValue)) {
                    $relationAttributes = json_decode($fieldValue, true);
                    if (json_last_error()) {
                        throw new WhereException("JSON parse error of value $relationName param! Error message: " . json_last_error_msg());
                    }
                    foreach ($relationAttributes as $relationAttributeName => $relationAttributeValue) {
                        $this->setWhereHasValue($relationName, $relationAttributeName, $relationAttributeValue);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Точный поиск по определенному relation
     *
     * @param string $relationName
     * @param string $field
     * @param $value
     * @param string $operator
     * @return $this
     */
    private function setWhereHasValue(string $relationName, string $field, $value, string $operator = '=')
    {
        $relationName = Str::camel($relationName);
        if ($this->countWhereHas == 0) {
            $this->whereHas($relationName, function ($query) use ($field, $value, $operator) {
                $query->where($field, $value, $operator);
            });
        } else {
            $this->orWhereHas($relationName, function ($query) use ($field, $value, $operator) {
                $query->where($field, $value, $operator);
            });
        }
        $this->countWhereHas++;
        return $this;
    }

    /**
     * Неточный поиск по всем fields модели
     *
     * domain/Service/Entity/getItems?_full_search=string
     * Ищет все Entity где какое-то из полей ILIKE %string%
     *
     * @param string $fullSearchString
     * @return $this
     */
    public function setFullSearch(string $fullSearchString)
    {
        if (!$this->metadata->getSupportFullSearch()) {
            return $this;
        }
        if (empty($fullSearchString)) {
            return $this;
        }
        $fields = array_keys($this->metadata->getData()['fields']);

        foreach ($fields as $key => $field) {
            if ($this->metadata->isFake($field)) {
                continue;
            }
            $key === 0
                ? $this->where($field, 'ILIKE', '%' . $fullSearchString . '%')
                : $this->orWhere($field, 'ILIKE', '%' . $fullSearchString . '%');
        }
        return $this;
    }

    /**
     * Точный поиск по определенному field
     *
     * @param string $fieldName
     * @param $fieldValue
     * @return $this
     */
    public function setWhereValue(string $fieldName, $fieldValue)
    {
        if (is_array($fieldValue)) {
            $nulls = Arr::where($fieldValue, function ($fieldValue, $key) use ($fieldName) {
                if (is_null($this->metadata->getField($fieldName))) {
                    throw new WhereException("The $fieldName field does not exist!");
                }
                return strtolower($fieldValue) === 'null' || $fieldValue === null;
            });
            foreach ($nulls as $nullKey => $nullElement) {
                unset($fieldValue[$nullKey]);
            }
            $this->whereIn($fieldName, $fieldValue);
            if (isset($nulls) && is_array($nulls) && !empty($nulls) && count($nulls) > 0) {
                $this->orWhereNull($fieldName);
            }
        } elseif (strtolower($fieldValue) === 'null') {
            $this->whereNull($fieldName);
        } else {
            $this->where($fieldName, '=', $fieldValue);
        }
        return $this;
    }

    /**
     * @return MetadataInterface
     */
    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }

    /**
     * @param MetadataInterface $metadata
     * @return $this
     */
    public function setMetadata(MetadataInterface $metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * Поиск записей с значение поля больше введенного значения
     *
     * domain/Service/Entity/getItems?_range_from={"field": "value"|value}
     * Ищет все Entity где field.value >= value
     *
     * @param array $getsFrom
     * @return $this
     */
    public function setGetsFrom(array $getsFrom)
    {
        foreach ($getsFrom as $field => $value) {
            $this->setGetFrom($field, $value);
        }
        return $this;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    public function setGetFrom(string $field, $value)
    {
        $this->where($field, '>=', $value);
        return $this;
    }

    /**
     * Поиск записей с значение поля больше введенного значения
     *
     * domain/Service/Entity/getItems?_range_to={"field": "value"|value}
     * Ищет все Entity где field.value >= value
     *
     * @param array $getsFrom
     * @return $this
     */
    public function setGetsTo(array $getsFrom)
    {
        foreach ($getsFrom as $field => $value) {
            $this->setGetTo($field, $value);
        }
        return $this;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    public function setGetTo(string $field, $value)
    {
        $this->where($field, '<=', $value);
        return $this;
    }

    /**
     * Данный метод можно переопределить
     * для добавления дополнительных параметров при выборке сущностей Entity
     *
     * @return $this
     */
    public function setAdditionalParams()
    {
        return $this;
    }

    /**
     * Функция получения первого элемента,
     * если элемента по текущей выборке не существует выбрасывать NotFoundException
     *
     * @return Builder|\Illuminate\Database\Eloquent\Model|object
     * @throws NotFoundException
     */
    public function firstOrNotFoundException()
    {
        $result = $this->first();
        if (is_null($result)) {
            throw new NotFoundException();
        }
        return $result;
    }

    /**
     * @param $relationName
     * @throws ReflectionException
     */
    public function hasRelationOrFail($relationName): void
    {
        $model = $this->getModel();
        $modelClass = get_class($model);
        if (!method_exists($modelClass, $relationName)) {
            throw RelationNotFoundException::make($model, $relationName);
        }
        $returnType = (new ReflectionMethod($modelClass, $relationName))->getReturnType();
        if (is_null($returnType)) {
            throw RelationNotFoundException::make($model, $relationName);
        }
        $returnTypeName = $returnType->getName();
        if (!class_exists($returnTypeName) || !is_a($returnTypeName, Relation::class, true)) {
            throw RelationNotFoundException::make($model, $relationName);
        }
    }

}
