<?php

namespace EgalFramework\Model\Tests\Samples\Stubs;

use EgalFramework\Common\Interfaces\FilterQueryInterface;
use Exception;

class FilterQuery implements FilterQueryInterface
{

    private string $fullSearch;
    private array $fields;
    private array $substringSearch;
    private array $from;
    private array $to;
    private int $limitFrom;
    private int $limitCount;
    private string $orderBy;
    private array $order;
    private array $relationId;
    private string $relationModel;

    /**
     * @param array $query
     * @throws Exception
     */
    public function setQuery(array $query): void
    {
        $this->substringSearch = $this->from = $this->to = $this->fields = [];
        $this->limitFrom = 0;
        $this->limitCount = 10;
        $this->orderBy = 'id';
        $this->order = 'ASC';
        $this->relationModel = $this->fullSearch = '';
        foreach ($query as $key => $value) {
            $value = trim($value);
            if ($value === '') {
                continue;
            }
            switch ($key) {
                case '_full_search':
                    $this->fullSearch = $value;
                    break;
                case '_search':
                    $this->substringSearch = $this->extractSearch($value);
                    break;
                case '_from':
                    $value = (int)$value;
                    $this->limitFrom = ($value < 0)
                        ? 0
                        : $value;
                    break;
                case '_count':
                    $this->extractLimitCount($value);
                    break;
                case '_order_by':
                    $this->orderBy = $value;
                    break;
                case '_order':
                    $this->order = $value;
                    break;
                case '_range_from':
                    $this->from = $this->extractSearch($value);
                    break;
                case '_range_to':
                    $this->to = $this->extractSearch($value);
                    break;
                case '_rel_id':
                    $this->relationId = [(int)$value];
                    break;
                case '_rel_model':
                    $this->relationModel = $value;
                    break;
                default:
                    $this->fields[$key] =
                        ((substr($value, 0, 1) == '[') && (substr($value, strlen($value) - 1) == ']'))
                            ? explode(',', trim($value, '[]'))
                            : $value;
            }
        }
    }

    /**
     * @param string $str
     * @return array
     * @throws Exception
     */
    private function extractSearch(string $str)
    {
        $result = [];
        $data = json_decode($str, TRUE);
        if (json_last_error()) {
            throw new Exception('Unable to parse JSON: ' . json_last_error_msg() . ' => ' . $str);
        }
        foreach ($data as $key => $value) {
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * @param string $value
     */
    private function extractLimitCount(string $value)
    {
        $value = (int)$value;
        if ($value > 100) {
            $value = 100;
        }
        $this->limitCount = ($value < 1)
            ? 1
            : $value;
    }

    public function getMaxCount(): int
    {
        return 100;
    }

    public function getFields(bool $original = false): array
    {
        return $this->fields;
    }

    public function getSubstringSearch(bool $original = false): array
    {
        return $this->substringSearch;
    }

    public function getOrderBy(bool $original = false): string
    {
        return $this->orderBy;
    }

    public function getOrder(bool $original = false): array
    {
        return $this->order;
    }

    public function getFrom(bool $original = false): array
    {
        return $this->from;
    }

    public function getTo(bool $original = false): array
    {
        return $this->to;
    }

    public function getLimitFrom(bool $original = false): int
    {
        return $this->limitFrom;
    }

    public function getLimitCount(bool $original = false): int
    {
        return $this->limitCount;
    }

    public function getFullSearch(bool $original = false): string
    {
        return $this->fullSearch;
    }

    public function getRelationModel(bool $original = false): string
    {
        return $this->relationModel;
    }

    public function getRelationId(bool $original = false): array
    {
        return $this->relationId;
    }

    public function getField(string $name, bool $original = false): ?string
    {
        // TODO: Implement getField() method.
    }

    public function setMaxCount(int $maxCount): void
    {
        // TODO: Implement setMaxCount() method.
    }

    public function getDefaultCount(): int
    {
        // TODO: Implement getDefaultCount() method.
    }

    public function setDefaultCount(int $defaultCount): void
    {
        // TODO: Implement setDefaultCount() method.
    }

    public function setFields(array $fields): void
    {
        // TODO: Implement setFields() method.
    }

    public function setField(string $name, $value): void
    {
        // TODO: Implement setField() method.
    }

    public function setSubstringSearch(array $value): void
    {
        // TODO: Implement setSubstringSearch() method.
    }

    public function setOrderBy(string $value): void
    {
        // TODO: Implement setOrderBy() method.
    }

    public function setOrder(array $value): void
    {
        // TODO: Implement setOrder() method.
    }

    public function setFrom(array $from): void
    {
        // TODO: Implement setFrom() method.
    }

    public function setTo(array $to): void
    {
        // TODO: Implement setTo() method.
    }

    public function setLimitFrom(int $from): void
    {
        // TODO: Implement setLimitFrom() method.
    }

    public function setLimitCount(int $count): void
    {
        // TODO: Implement setLimitCount() method.
    }

    public function setFullSearch(string $value): void
    {
        // TODO: Implement setFullSearch() method.
    }

    public function setRelationModel(string $model): void
    {
        // TODO: Implement setRelationModel() method.
    }

    public function setRelationId(array $ids): void
    {
        // TODO: Implement setRelationId() method.
    }

    public function getWith(bool $original = false): array
    {
        // TODO: Implement getWith() method.
    }

    public function setWith(array $with): void
    {
        // TODO: Implement setWith() method.
    }
}
