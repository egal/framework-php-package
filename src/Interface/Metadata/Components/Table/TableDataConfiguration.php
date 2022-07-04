<?php

namespace Egal\Interface\Metadata\Components\Table;

use Egal\Core\Rest\Filter\Query as FilterQuery;
use Egal\Interface\Metadata\Configuration;

class TableDataConfiguration extends Configuration
{
    protected array $fields;

    protected string $requestModel;

    protected ?array $requestRelations;

    protected ?FilterQuery $requestFilter;

    protected ?array $requestOrder;

    public static function make():self
    {
        return new self();
    }

    public function getFields(): array
    {
        return $this->getFieldsArray();
    }

    public function getRequestModel(): string
    {
        return $this->requestModel;
    }

    public function getRequestRelations(): ?array
    {
        return $this->requestRelations;
    }

    public function getRequestFilter(): ?FilterQuery
    {
        return $this->requestFilter;
    }

    public function getRequestOrder(): ?array
    {
        return $this->requestOrder;
    }

    public function setFields(TableField ...$fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function setRequestModel(string $requestModel): self
    {
        $this->requestModel = $requestModel;

        return $this;
    }

    public function setRequestRelations(?array $requestRelations): self
    {
        $this->requestRelations = $requestRelations;

        return $this;
    }

    public function setRequestFilter(?FilterQuery $requestFilter): self
    {
        $this->requestFilter = $requestFilter;

        return $this;
    }

    public function setRequestOrder(?array $requestOrder): self
    {
        $this->requestOrder = $requestOrder;

        return $this;
    }

    public function getFieldsArray(): array
    {
        $fieldsArray = [];
        foreach ($this->fields as $field) {
            $fieldsArray[] = $field->toArray();
        }

        return $fieldsArray;
    }
}
