<?php

namespace EgalFramework\Model\Deprecated;

use EgalFramework\Common\Interfaces\RelationDirectionInterface;
use Ramsey\Uuid\Uuid;

/**
 * @deprecated
 *
 * Class Tree
 * @package EgalFramework\Model\Deprecated
 */
class Tree
{

    /** @var Tree[] */
    private array $children;

    /** @var int */
    private int $id;

    /** @var string */
    private string $label;

    /** @var RelationDirectionInterface */
    private RelationDirectionInterface $direction;

    /**
     * Tree constructor.
     * @param int $id
     * @param string $label
     */
    public function __construct(int $id = 0, string $label = '')
    {
        $this->id = $id;
        $this->label = $label;
        $this->children = [];
    }

    /**
     * @param int $id
     * @param string $label
     * @return $this
     */
    public function add(int $id, string $label)
    {
        return $this->children[] = new Tree($id, $label);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param RelationDirectionInterface $direction
     * @return $this
     */
    public function setDirection(RelationDirectionInterface $direction)
    {
        $this->direction = $direction;
        return $this;
    }

    /**
     * @TODO $topId is K&V style
     * @param int $topId
     * @return array
     */
    public function toArray(int $topId = 0)
    {
        $isParent = empty($this->id);
        $id = ($topId == 0 && !$isParent)
            ? $this->id
            : Uuid::uuid4();
        $result = $this->fillBasicFields($id, $topId);
        if (!empty($this->children)) {
            $result['children'] = [];
            foreach ($this->children as $child) {
                $result['children'][] = $child->toArray(
                    ($topId == 0 && !$isParent)
                        ? $this->getId()
                        : $topId
                );
            }
        }
        return $isParent
            ? (
            empty($result['children'])
                ? []
                : $result['children']
            )
            : $result;
    }

    /**
     * @param string $id
     * @param string $topId
     * @return array
     */
    private function fillBasicFields(string $id, string $topId)
    {
        return array_filter([
            'id' => $id,
            'label' => $this->label,
            'topId' => $topId,
            'direction' => isset($this->direction)
                ? $this->direction->toArray()
                : '',
        ]);
    }

}
