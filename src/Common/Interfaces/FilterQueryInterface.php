<?php

namespace EgalFramework\Common\Interfaces;

interface FilterQueryInterface
{

    public function getMaxCount(): int;

    public function setMaxCount(int $maxCount): void;

    public function getDefaultCount(): int;

    public function setDefaultCount(int $defaultCount): void;

    public function setQuery(array $query): void;

    public function getFields(bool $original = false): array;

    public function setFields(array $fields): void;

    /**
     * @param string $name
     * @param bool $original
     * @return mixed
     */
    public function getField(string $name, bool $original = false);

    public function setField(string $name, $value): void;

    public function getSubstringSearch(bool $original = false): array;

    public function setSubstringSearch(array $value): void;

    public function getOrder(bool $original = false): array;

    public function setOrder(array $value): void;

    public function getFrom(bool $original = false): array;

    public function setFrom(array $from): void;

    public function getTo(bool $original = false): array;

    public function setTo(array $to): void;

    public function getLimitFrom(bool $original = false): int;

    public function setLimitFrom(int $from): void;

    public function getLimitCount(bool $original = false): int;

    public function setLimitCount(int $count): void;

    public function getFullSearch(bool $original = false): string;

    public function setFullSearch(string $value): void;

    public function getRelationModel(bool $original = false): string;

    public function setRelationModel(string $model): void;

    public function getRelationId(bool $original = false): array;

    public function setRelationId(array $ids): void;

    /**
     * @param bool $original
     * @return string[]
     */
    public function getWith(bool $original = false): array;

    /**
     * @param string[] $with
     */
    public function setWith(array $with): void;

}
