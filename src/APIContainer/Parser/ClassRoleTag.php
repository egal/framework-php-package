<?php

namespace EgalFramework\APIContainer\Parser;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tags\BaseTag;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\StaticMethod;
use phpDocumentor\Reflection\Types\Context;
use Webmozart\Assert\Assert;

/**
 * Class RoleTag
 * @package EgalFramework\APIContainer\Parser
 */
final class ClassRoleTag extends BaseTag implements StaticMethod
{

    protected $name = 'method-roles';

    /** @var string */
    protected string $methodName;

    /** @var string[] */
    protected array  $roles;

    /**
     * @param string $methodName
     * @param string $roles
     * @param Description $description
     */
    public function __construct(string $methodName, string $roles, Description $description = NULL)
    {
        $this->methodName = $methodName;
        $this->description = $description;
        $this->roles = array_filter(array_map('trim', explode(',', $roles)));
    }

    /**
     * @param string $body
     * @param ?DescriptionFactory $descriptionFactory
     * @param Context|NULL $context
     * @return $this
     */
    public static function create(
        string $body, DescriptionFactory $descriptionFactory = null, Context $context = null
    ): self
    {
        $data = preg_split('/\b\s+/', $body);
        $methodName = empty($data[0])
            ? ''
            : $data[0];
        $roles = empty($data[1])
            ? ''
            : $data[1];
        Assert::notNull($descriptionFactory);
        Assert::notEmpty($methodName);
        return new static($methodName, $roles, $descriptionFactory->create($body, $context));
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return trim($this->methodName . ' ' . implode(',', $this->roles) . ' ' . (string)$this->description);
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

}
