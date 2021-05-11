<?php

namespace EgalFramework\Model\Traits;

use EgalFramework\Model\Builder;
use EgalFramework\Model\Exceptions\HashGuardException;
use Illuminate\Support\Facades\Schema;

/**
 * Trait используется для защиты данных с помощью проверки hash объекта.
 *
 * Для включения защиты объектов, нужно включить данный Trait в смою Model.
 *
 * По стандарту защищаются все атрибуты, кроме полей timestamps и поля хранящего hash.
 * Для формирования точно списка защищенных полей -
 * нужно переопределить $hashShieldingFields.
 * Атрибут $hashShieldingFields может принимать значение ['*'], что будет означать - защита всех полей
 * Для добавления дополнительных полей, которые нужно исключить из списка защищенных -
 * нужно переопределить $ignoreHashShieldingFields атрибут.
 *
 * Trait HashGuard
 * @package EgalFramework\Model\Traits
 */
trait HashGuard
{

    /**
     * @var string[]
     */
    protected array $hashShieldingFields = ['*'];

    /**
     * @var string[]
     */
    protected array $ignoreHashShieldingFields = [];

    public function initializeHashGuard()
    {
        $this->computeHashShieldingFields();
    }

    /**
     * @throws HashGuardException
     */
    public static function bootHashGuard()
    {
        static::saved(function ($model) {
            /** @var static $model */
            $model->refresh();
            $model->setHash($model->makeHash());
            $model->saveQuietly();
        });

        static::got(function ($model) {
            /** @var static $model */
            $model->checkHash();
        });
    }

    public function getHash()
    {
        return $this->{$this->getHashFieldName()};
    }

    public function setHash($hash)
    {
        $this->{$this->getHashFieldName()} = $hash;
        return $this;
    }

    /**
     * Получение названия field для хранения hash данных модели
     *
     * Данную функцию можно переопределить,
     * для изменения названия field для хранения hash данных модели
     *
     * @return string
     */
    public function getHashFieldName()
    {
        return 'hash';
    }

    /**
     * Проверка возможности проведения защиты
     *
     * @throws HashGuardException
     */
    public function checkMyUsesHashShieldingFields()
    {
        if (!$this->metadata->getField($this->getHashFieldName())) {
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
     *
     * @return string
     * @throws HashGuardException
     */
    protected function makeHash()
    {
        $this->checkMyUsesHashShieldingFields();

        // Получаем данные модели, которые будем использовать для генерации hash,
        // меняя местами ключи и значения у $this->hashShieldingFields
        // и находя общие значения между получившимся массивом и $this->attributesToArray() по ключам
        $toHashAttributes = array_intersect_key($this->attributesToArray(), array_flip($this->hashShieldingFields));

        return hash('SHA256', json_encode($toHashAttributes));
    }

    /**
     * Вычисление полей требуемых для hash защиты данных модели
     * (вычисление $this->hashShieldingFields)
     *
     * @return $this
     */
    public function computeHashShieldingFields()
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
            $this->hashShieldingFields = $this->metadata->getFieldNames(true);
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
     *
     * @throws HashGuardException
     */
    public function checkHash()
    {
        if ($this->getHash() !== $this->makeHash()) {
            throw new HashGuardException('Data check hash failed!');
        }
    }

    /**
     * @param array $ids
     * @throws HashGuardException
     */
    public static function rehash($ids = [])
    {
        static::withoutEvents(function () use ($ids) {
            /** @var Builder $query */
            $query = static::query();
            if (!empty($ids)) {
                $query->whereIn('id', $ids);
            }
            $items = $query->get();
            $items->each(function ($item) {
                /** @var static $item */
                $item->setHash($item->makeHash());
                $item->saveQuietly();
            });
        });
    }

}