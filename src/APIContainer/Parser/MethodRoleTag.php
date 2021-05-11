<?php

namespace EgalFramework\APIContainer\Parser;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tags\BaseTag;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\StaticMethod;
use phpDocumentor\Reflection\Types\Context;
use Webmozart\Assert\Assert;

/**
 * Class MethodRoleTag
 * @package EgalFramework\APIContainer\Parser
 */
final class MethodRoleTag extends BaseTag implements StaticMethod
{

    /** @var string */
    protected $name = 'roles';

    /** @var string[] */
    protected $roles;

    public function __construct(string $roles, Description $description = NULL)
    {
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
        Assert::notNull($descriptionFactory);
        $data = preg_split('/\b\s+/', $body);
        $roles = empty($data[0])
            ? ''
            : $data[0];
        return new static($roles, $descriptionFactory->create($body, $context));
    }

    public function __toString(): string
    {
        return trim(implode(',', $this->roles) . ' ' . (string)$this->description);
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

}
