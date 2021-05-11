<?php

namespace EgalFramework\APIContainer\Parser;

use EgalFramework\APIContainer\Models\Argument;
use EgalFramework\APIContainer\Models\Method;
use EgalFramework\Common\Interfaces\APIContainer\MethodInterface;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Param as PDBTagParam;
use phpDocumentor\Reflection\DocBlock\Tags\Return_ as PDBTagReturn;
use phpDocumentor\Reflection\DocBlockFactory;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Class MethodDescription
 *
 * Extract API from class's methods
 * @package EgalFramework\APIContainer\Parser
 */
class MethodDescription
{

    /**
     * @param ReflectionClass $reflectionClass
     * @return array
     */
    public function extract(ReflectionClass $reflectionClass)
    {
        $methods = [];
        foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (preg_match('/^__/', $method->getName())) {
                continue;
            }
            $comment = $method->getDocComment();
            if (!$comment) {
                continue;
            }
            $factory = DocBlockFactory::createInstance(['roles' => MethodRoleTag::class]);
            $dbp = $factory->create($comment);

            $methodObj = new Method;
            $methodObj->name = $method->getName();
            $methodObj->summary = $dbp->getSummary();
            $methodObj->description = $dbp->getDescription()->render();
            $methodObj->fromClass = $method->getDeclaringClass()->getName();
            $methods[$methodObj->name] = $methodObj;
            $this->extractTags(
                $methods[$methodObj->name], $method, $dbp->getTags()
            );
        }
        return $methods;
    }

    /**
     * @param MethodInterface $method
     * @param ReflectionMethod $reflectionMethod
     * @param Tag[] $tags
     */
    private function extractTags(MethodInterface $method, ReflectionMethod $reflectionMethod, array $tags)
    {
        $reflectionParams = $this->getReflectionParams($reflectionMethod);
        foreach ($tags as $tag) {
            if ($tag instanceof PDBTagParam) {
                $argument = $this->extractArgument(
                    $tag,
                    empty($reflectionParams[$tag->getVariableName()])
                        ? NULL
                        : $reflectionParams[$tag->getVariableName()]
                );
                $method->arguments[$argument->name] = $argument;
            } elseif ($tag instanceof PDBTagReturn) {
                $method->return = $this->extractReturn($tag);
            } elseif ($tag instanceof MethodRoleTag) {
                $method->roles = $tag->getRoles();
            }
        }
    }

    /**
     * @param ReflectionMethod $reflectionMethod
     * @return ReflectionParameter[]
     */
    private function getReflectionParams(ReflectionMethod $reflectionMethod)
    {
        $result = [];
        $params = $reflectionMethod->getParameters();
        foreach ($params as $param) {
            $result[$param->getName()] = $param;
        }
        return $result;
    }

    /**
     * @param PDBTagParam $tag
     * @param ReflectionParameter|null $reflectionParam
     * @return Argument
     */
    private function extractArgument(PDBTagParam $tag, $reflectionParam)
    {
        $argument = new Argument;
        $argument->name = $tag->getVariableName();
        $argument->type = (string)$tag->getType();
        $argument->description = (string)$tag->getDescription();
        $argument->isRequired = is_null($reflectionParam)
            ? true
            : !$reflectionParam->isOptional();
        return $argument;
    }

    /**
     * @param PDBTagReturn $tag
     * @return string
     */
    private function extractReturn(PDBTagReturn $tag)
    {
        return preg_replace(
            '{\bstatic(\[])?\b}',
            '$this$1',
            (string)$tag->getType()
        );
    }

}
