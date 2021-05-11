<?php

namespace EgalFramework\Metadata\Models;

use EgalFramework\Common\Registry;
use EgalFramework\Common\Session;
use EgalFramework\Metadata\Endpoints;
use EgalFramework\Metadata\Exception;
use EgalFramework\Metadata\Metadata as AMetadata;
use Illuminate\Support\Facades\Cache;

/**
 * Class MetaData
 * @package App\PublicModels
 */
class Metadata
{

    /**
     * @return array
     * @throws Exception
     * @roles @all
     */
    public function getAll()
    {
        $roles = Session::getRoleManager()->getRoles();
        if (empty($roles)) {
            $roles = [];
        }
        sort($roles);
        $cacheKey = 'MetadataCache|' . implode('|', $roles);
        if ($cache = Session::getRegistry()->get($cacheKey)) {
            return $cache;
        }
        $result = [];
        $endpoints = new Endpoints(Session::getApiStorage());
        foreach (Session::getModelManager()->getModels() as $modelName) {
            if ($modelName === 'Metadata' || !class_exists(Session::getModelManager()->getMetadataPath($modelName))) {
                continue;
            }
            $result[$modelName] = $this->getModel($modelName);
            $endpoints->addClass(
                $modelName, empty($roles)
                ? []
                : $roles
            );
        }
        $resultData = [
            'metadata' => $result,
            'menu' => (Session::getMenu())->build(),
            'endpoints' => $endpoints->endpoints
        ];
        Session::getRegistry()->set($cacheKey, $resultData);
        return $resultData;
    }

    /**
     * @param string $modelName
     * @return AMetadata[]
     * @throws Exception
     * @roles @all
     */
    public function getModel(string $modelName)
    {
        if (!Session::getModelManager()->hasMetadata($modelName)) {
            throw new Exception('Metadata for ' . $modelName . ' does not exist');
        }
        $className = Session::getModelManager()->getMetadataPath($modelName);
        /** @var AMetadata $object */
        $object = new $className;
        return $object->getData();
    }

}
