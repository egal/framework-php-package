<?php

namespace EgalFramework\Model;

use EgalFramework\Common\Interfaces\MetadataInterface;
use EgalFramework\Model\Casts\XssShieldingCast;
use EgalFramework\Model\Exceptions\NotFoundException;
use EgalFramework\Model\Traits\HasEvents;
use EgalFramework\Model\Traits\UsesEgalBuilder;
use EgalFramework\Model\Traits\HasMetadata;
use EgalFramework\Model\Traits\UsesValidator;
use Exception;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use EgalFramework\Common\Session;
use ReflectionClass;
use ReflectionException;

/**
 * Class Model
 * @package EgalFramework\Model
 */
abstract class Model extends EloquentModel
{

    use UsesEgalBuilder,
        HasMetadata,
        UsesValidator,
        HasEvents;

    protected MetadataInterface $metadata;
    protected string $className;

    /**
     * Model constructor.
     *
     * @param array $attributes
     * @throws ReflectionException
     */
    public function __construct(array $attributes = [])
    {
        $this->className = (new ReflectionClass($this))->getShortName();
        $this->metadata = Session::getMetadata($this->className);
        $this->table = $this->metadata->getTable();
        parent::__construct($attributes);
    }

    /**
     * Endpoint получения сущностей
     *
     * service/Entity/getItem/{id}
     * service/Entity/actionGetItem/{id}
     * где Query params одно из: field_name, _full_search, _search, _order, _range_from, _range_to, _with
     *
     * @return array
     * @throws Exceptions\OrderException
     * @throws ReflectionException
     * @throws Exceptions\WhereException
     */
    public static function actionGetItems(): array
    {
        $filterQuery = Session::getFilterQuery();
        $filterQuery->setQuery(Session::getMessage()->getQuery());
        if ($defaultMaxCount = static::getMetadata()->getDefaultMaxCount()) {
            $filterQuery->setMaxCount($defaultMaxCount);
        }

        $paginator = static::query()
            ->setMetadata(static::getMetadata())
            ->setWhereValues($filterQuery->getFields()) // Query param: field_name
            ->setFullSearch($filterQuery->getFullSearch()) // Query param: _full_search
            ->setSubstringSearchValues($filterQuery->getSubstringSearch()) // Query param: _search
            ->setOrder($filterQuery->getOrder()) // Query param: _order
            ->setGetsFrom($filterQuery->getFrom()) // Query param: _range_from.
            ->setGetsTo($filterQuery->getTo()) // Query param: _range_to
            ->with($filterQuery->getWith()) // Query param: _with
            ->withCasts([
                XssShieldingCast::class
            ])
            ->setAdditionalParams() // Дополнительные запросы
            ->paginate(
                $filterQuery->getLimitCount(),
                ['*'],
                'page',
                ceil($filterQuery->getLimitFrom() / $filterQuery->getLimitCount()) # TODO: Перейти на Query param: _page
            );

        /** @var static $item */
        foreach ($paginator->items() as $item) {
            $item->fireModelEvent('got');
        }

        return [
            'current_page' => $paginator->currentPage(),
            'total_count' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'items' => $paginator->items(),
        ];
    }

    /**
     * Endpoint получения сущности
     *
     * service/Entity/getItem/{id}
     * service/Entity/actionGetItem/{id}
     * где Query params одно из: _with
     *
     * @return array
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public static function actionGetItem(): array
    {
        $filterQuery = Session::getFilterQuery();
        $filterQuery->setQuery(Session::getMessage()->getQuery());

        $item = static::query()
            ->setMetadata(static::getMetadata())
            ->where('id', '=', Session::getMessage()->getId())
            ->with($filterQuery->getWith()) // Query param: _with
            ->setAdditionalParams() // Дополнительные запросы
            ->firstOrNotFoundException();

        $item->fireModelEvent('got');

        return $item->toArray();
    }

    /**
     * Endpoint создания сущности
     *
     * service/Entity/create
     * service/Entity/actionCreate
     * где data/raw = [{attributes}]
     *
     * @param array $attributes
     * @return array
     */
    public static function actionCreate(array $attributes = []): array
    {
        $entity = new static();
        $entity->fill($attributes);
        $entity->save();
        $entity->refresh();

        return $entity->toArray();
    }

    /**
     * Endpoint обновления сущности
     *
     * service/Entity/update/{id}
     * service/Entity/actionUpdate/{id}
     * где data/raw = [{attributes}]
     *
     * @param array $attributes
     * @return array
     * @throws NotFoundException
     * @throws ReflectionException
     */
    public static function actionUpdate(array $attributes = []): array
    {
        $entity = static::query()
            ->setMetadata(static::getMetadata())
            ->where('id', '=', Session::getMessage()->getId())
            ->setAdditionalParams()
            ->firstOrNotFoundException();

        $entity->fill($attributes);
        $entity->save();

        return $entity->toArray();
    }

    /**
     * Endpoint удаления сущности
     *
     * service/Entity/delete/{id}
     * service/Entity/actionDelete/{id}
     *
     * @return bool|mixed|null
     * @throws NotFoundException
     * @throws Exception
     */
    public static function actionDelete()
    {
        $entity = static::query()
            ->setMetadata(static::getMetadata())
            ->where('id', '=', Session::getMessage()->getId())
            ->setAdditionalParams()
            ->firstOrNotFoundException();

        return $entity->delete();
    }

}
