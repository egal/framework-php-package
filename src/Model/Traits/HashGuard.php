<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

use Egal\Model\Exceptions\HashGuardException;
use Egal\Model\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Trait используется для защиты данных с помощью проверки hash объекта.
 *
 * Для включения защиты объектов, нужно включить данный Trait в свою Model.
 *
 * По стандарту защищаются все атрибуты, кроме полей timestamps и поля хранящего hash.
 * Для формирования точно списка защищенных полей -
 * нужно переопределить $hashShieldingFields.
 * Атрибут $hashShieldingFields может принимать значение ['*'], что будет означать - защита всех полей
 * Для добавления дополнительных полей, которые нужно исключить из списка защищенных -
 * нужно переопределить $ignoreHashShieldingFields атрибут.
 *
 * @mixin Model
 */
trait HashGuard
{

    public function initializeHashGuard()
    {
        # TODO: Проверить используется ли в static HashGradable
        $this->computeHashShieldingFields();
    }

    public static function bootHashGuard()
    {
        static::saved(function (self $model) {
            $model->refresh();
            $model->setHash($model->makeHash());
            $model->saveQuietly();
        });
        static::retrieved(function (self $model) {
            $model->checkHash();
        });
    }

    public function getHash(): string
    {
        return $this->getAttribute($this->getHashFieldName());
    }

    public function setHash(string $hash): self
    {
        $this->setAttribute($this->getHashFieldName(), $hash);

        return $this;
    }

    /**
     * Проверка возможности проведения защиты
     */
    public function mayUsesHashGuardOrFail()
    {
        if (!$this->getModelMetadata()->fieldExist($this->getHashFieldName())) {
            throw new HashGuardException(
                'Missing ' . $this->getHashFieldName() . ' field in Metadata!'
            );
        }

        if (!Schema::hasColumn($this->getTable(), $this->getHashFieldName())) {
            throw new HashGuardException(
                'Missing ' . $this->getHashFieldName() . ' field in database!'
            );
        }
    }

    /**
     * Генерация hash данных модели
     */
    protected function makeHash(): string
    {
        $this->mayUsesHashGuardOrFail();

        // Получаем данные модели, которые будем использовать для генерации hash,
        // меняя местами ключи и значения у $this->hashShieldingFields
        // и находя общие значения между получившимся массивом и $this->attributesToArray() по ключам
        $toHashAttributes = array_intersect_key($this->attributesToArray(), array_flip($this->hashShieldingFields));

        return hash('SHA256', json_encode($toHashAttributes));
    }

    /**
     * Вычисление полей требуемых для hash защиты данных модели
     */
    public function computeHashShieldingFields(): self
    {
        # TODO: Неправильно оставлять поля timestamps без защиты (при выставлении hash они изменяются LaravelModel)
        // Описываем поля, которые по стандарту игнорируются проверкой
        $defaultIgnoreHashShieldingFields = [
            $this->getHashFieldName(),
            $this::CREATED_AT,
            $this::UPDATED_AT
        ];

        // Если указано в списке полей требуемых защиты указано '*' - выставляем на проверку все поля
        if ($this->hashShieldingFields == ['*']) {
            $this->hashShieldingFields = $this->getModelMetadata()->getFields();
        }

        // Получаем итоговый список полей, которые нужно защитить
        // путём нахождения разницы выставленных полей для защиты и полей не требующих защиты
        $this->hashShieldingFields = array_diff(
            $this->hashShieldingFields,
            $this->ignoreHashShieldingFields,
            $defaultIgnoreHashShieldingFields
        );

        return $this;
    }

    /**
     * Проверка совпадения текущего hash и который должен быть
     */
    public function checkHash()
    {
        if ($this->getHash() !== $this->makeHash()) {
            throw new HashGuardException('Data check hash failed!');
        }
    }

    /**
     * TODO: Сделать консольную команду rehash
     */
    public static function rehash(array $ids = [])
    {
        static::withoutEvents(function () use ($ids) {
            $query = static::query();

            if (!empty($ids)) {
                $query->whereIn('id', $ids);
            }

            $items = $query->get();
            $items->each(function (self $item) {
                $item->setHash($item->makeHash());
                $item->saveQuietly();
            });
        });
    }

}
