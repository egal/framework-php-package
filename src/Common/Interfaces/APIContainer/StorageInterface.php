<?php

namespace EgalFramework\Common\Interfaces\APIContainer;

interface StorageInterface
{

    /**
     * @param ModelInterface $class
     */
    public function save(ModelInterface $class);

    /**
     * Get class description
     * @param string $model
     * @return ModelInterface
     */
    public function getClass(string $model);

    /**
     * Save class description
     * @param ModelInterface $class
     */
    public function saveClass(ModelInterface $class);

    /**
     * Get method
     * @param string $model
     * @param string $method
     * @return MethodInterface
     */
    public function getMethod(string $model, string $method);

    /**
     * Save method
     * @param ModelInterface $class
     * @param MethodInterface $method
     */
    public function saveMethod(ModelInterface $class, MethodInterface $method);

    /**
     * Remove method
     * @param string $model
     * @param string $method
     */
    public function removeMethod(string $model, string $method);

    /**
     * Remove class at all
     * @param string $model
     */
    public function removeClass(string $model);

    /**
     * Remove all API records from DB
     */
    public function removeAll();


}
