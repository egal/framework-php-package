<?php

namespace EgalFramework\Common\Interfaces;

interface FieldInterface
{

    public function getType(): string;

    public function setReadonly(bool $readonly): self;

    public function getReadonly(): bool;

    public function getReadonlyOnCreate(): bool;

    public function getReadonlyOnChange(): bool;

    public function getRelation(): ?string;

    public function getDefaultSortOrder(): string;
}
