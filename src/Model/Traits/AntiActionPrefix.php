<?php

namespace EgalFramework\Model\Traits;

use Exception;

trait AntiActionPrefix
{

    /**
     * @deprecated
     * @return array
     */
    public function getItems(): array
    {
        return static::actionGetItems();
    }

    /**
     * @deprecated
     * @return array
     */
    public function getItem(): array
    {
        return static::actionGetItem();
    }

    /**
     * @deprecated
     * @param array $attributes
     * @return array
     */
    public function create(array $attributes = []): array
    {
        return static::actionCreate($attributes);
    }

    /**
     * @deprecated
     * @param array $attributes
     * @param array $options
     * @return mixed
     */
    public function update(array $attributes = [], array $options = [])
    {
        return static::actionUpdate($attributes);
    }

    /**
     * @return mixed
     * @throws Exception
     * @deprecated
     */
    public function delete()
    {
        if (empty($this->id)) {
            return static::actionDelete();
        } else {
            return parent::delete();
        }
    }

}