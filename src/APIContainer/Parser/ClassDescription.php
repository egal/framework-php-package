<?php

namespace EgalFramework\APIContainer\Parser;

use EgalFramework\APIContainer\Models\Argument;
use EgalFramework\APIContainer\Models\Method;
use EgalFramework\Common\Interfaces\APIContainer\MethodInterface;
use EgalFramework\Common\Interfaces\APIContainer\ModelInterface;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Method as PDBTagMethod;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;

/**
 * Class ClassDescription
 * Extract meta information from a class
 * @package EgalFramework\APIContainer\Parser
 */
class ClassDescription
{

    /** @var ReflectionClass */
    private ReflectionClass $reflectionClass;

    /** @var ModelInterface */
    private ModelInterface $model;

    /** @var bool */
    private bool $fullAccess;

    /**
     * ClassDescription constructor.
     * @param ReflectionClass $reflectionClass
     * @param ModelInterface $model
     * @param bool $fullAccess
     */
    public function __construct(ReflectionClass $reflectionClass, ModelInterface $model, bool $fullAccess)
    {
        $this->reflectionClass = $reflectionClass;
        $this->model = $model;
        $this->fullAccess = $fullAccess;
    }

    public function extract()
    {
        $comment = $this->reflectionClass->getDocComment();
        if (!empty($comment)) {
            $factory = DocBlockFactory::createInstance(['method-roles' => ClassRoleTag::class]);
            $dbp = $factory->create($comment);
            $methodDescription = new MethodDescription;
            foreach ($methodDescription->extract($this->reflectionClass) as $method) {
                $this->mergeMethods($method);
            }
            $this->model->summary = $dbp->getSummary();
            $this->model->description = $dbp->getDescription()->render();
            $this->extractMethods($dbp);
            $this->removeEmptyRolesMethods();
            $this->model->keySortMethods();
        }
    }

    /**
     * @param MethodInterface $method
     */
    private function mergeMethods(MethodInterface $method)
    {
        if (
            !is_null($this->model->getMethod($method->name))
            && !empty($this->model->getMethod($method->name)->roles)
            && empty($method->roles)
        ) {
            $method->roles = $this->model->getMethod($method->name)->roles;
        }
        $this->model->setMethod($method->name, $method);
    }

    /**
     * @param DocBlock $dbp
     */
    private function extractMethods(DocBlock $dbp)
    {
        foreach ($dbp->getTags() as $tag) {
            if ($tag instanceof PDBTagMethod) {
                $this->extractMethod($tag);
            } elseif ($tag instanceof ClassRoleTag) {
                $this->extractClassRoles($tag);
            }
        }
    }

    /**
     * @param PDBTagMethod $tag
     */
    private function extractMethod(PDBTagMethod $tag)
    {
        $name = $tag->getMethodName();
        $this->initMethod($name);
        $this->model->getMethod($name)->description = $tag->getDescription()->render();
        $this->model->getMethod($name)->arguments = $this->extractMethodArguments($tag);
    }

    /**
     * @param ClassRoleTag $tag
     */
    private function extractClassRoles(ClassRoleTag $tag)
    {
        $name = $tag->getMethodName();
        $this->initMethod($name);
        $this->model->getMethod($name)->roles = $tag->getRoles();
    }

    /**
     * @param string $name
     */
    private function initMethod(string $name)
    {
        if (is_null($this->model->getMethod($name))) {
            $this->model->setMethod($name, new Method());
            $this->model->getMethod($name)->name = $name;
        }
    }

    /**
     * @param PDBTagMethod $tag
     * @return array
     */
    private function extractMethodArguments(PDBTagMethod $tag)
    {
        $arguments = [];
        foreach ($tag->getArguments() as $argument) {
            $argumentObj = new Argument;
            $argumentObj->name = $argument['name'];
            $argumentObj->type = (string)$argument['type'];
            $argumentObj->isRequired = true;
            $arguments[$argumentObj->name] = $argumentObj;
        }
        return $arguments;
    }

    private function removeEmptyRolesMethods()
    {
        if ($this->fullAccess) {
            return;
        }
        foreach ($this->model->getMethods() as $key => $method) {
            if (empty($method->roles)) {
                $this->model->removeMethod($key);
            }
        }
    }

}
