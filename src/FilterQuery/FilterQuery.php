<?php

namespace EgalFramework\FilterQuery;

use EgalFramework\Common\Interfaces\FilterQueryInterface;

/**
 * Class API
 * Search form parser
 */
class FilterQuery implements FilterQueryInterface
{

    private int $maxCount;

    private int $defaultCount;

    private array $fields;

    private array $original;

    public function __construct()
    {
        $this->maxCount = Constants::SEARCH_COUNT_MAX;
        $this->defaultCount = Constants::SEARCH_COUNT_DEFAULT;
        $this->fields = [];
        $this->original = [];
    }

    public function setMaxCount(int $maxCount): void
    {
        $this->maxCount = $maxCount;
    }

    public function getMaxCount(): int
    {
        return $this->maxCount;
    }

    public function setDefaultCount(int $defaultCount): void
    {
        $this->defaultCount = $defaultCount;
    }

    public function getDefaultCount(): int
    {
        return $this->defaultCount;
    }

    // Fields

    public function getFields(bool $original = false): array
    {
        return $this->getFieldFromArray(Constants::FIELD_FIELDS, $original, []);
    }

    public function setFields(array $fields): void
    {
        $this->fields[Constants::FIELD_FIELDS] = $fields;
    }

    /**
     * @param string $name
     * @param bool $original
     * @return mixed
     */
    public function getField(string $name, bool $original = false)
    {
        return $this->getFieldFromSubarray(Constants::FIELD_FIELDS, $name, $original, '');
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setField(string $name, $value): void
    {
        $this->fields[Constants::FIELD_FIELDS][$name] = $value;
    }

    public function getLimitFrom(bool $original = false): int
    {
        return $this->getFieldFromArray(Constants::FIELD_LIMIT_FROM, $original, 0);
    }

    public function setLimitFrom(int $from): void
    {
        $this->fields[Constants::FIELD_LIMIT_FROM] = $from;
    }

    public function getLimitCount(bool $original = false): int
    {
        if (isset($this->fields[Constants::FIELD_LIMIT_COUNT])) {
            $count = $this->fields[Constants::FIELD_LIMIT_COUNT];
        } elseif ($this->maxCount === 0) {
            return 0;
        } else {
            $count = $this->defaultCount;
        }
        return (($count > $this->maxCount || $count <= 0) && $this->maxCount > 0)
            ? $this->maxCount
            : $count;
    }

    public function setLimitCount(int $count): void
    {
        $this->fields[Constants::FIELD_LIMIT_COUNT] = $count;
    }

    public function getFullSearch(bool $original = false): string
    {
        return $this->getFieldFromArray(Constants::FIELD_FULL_SEARCH, $original, '');
    }

    public function setFullSearch(string $value): void
    {
        $this->fields[Constants::FIELD_FULL_SEARCH] = $value;
    }

    /**
     * @param bool $original
     * @return string[]
     */
    public function getSubstringSearch(bool $original = false): array
    {
        return $this->getFieldFromArray(Constants::FIELD_SUBSTRING_SEARCH, $original, []);
    }

    public function setSubstringSearch(array $value): void
    {
        $this->fields[Constants::FIELD_SUBSTRING_SEARCH] = $value;
    }

    public function getOrder(bool $original = false): array
    {
        return $this->getFieldFromArray(Constants::FIELD_ORDER, $original, []);
    }

    public function setOrder(array $value): void
    {
        $this->fields[Constants::FIELD_ORDER] = $value;
    }

    /**
     * @param bool $original
     * @return string[]
     */
    public function getFrom(bool $original = false): array
    {
        return $this->getFieldFromArray(Constants::FIELD_RANGE_FROM, $original, []);
    }

    public function setFrom(array $from): void
    {
        $this->fields[Constants::FIELD_RANGE_FROM] = $from;
    }

    /**
     * @param bool $original
     * @return string[]
     */
    public function getTo(bool $original = false): array
    {
        return $this->getFieldFromArray(Constants::FIELD_RANGE_TO, $original, []);
    }

    public function setTo(array $to): void
    {
        $this->fields[Constants::FIELD_RANGE_TO] = $to;
    }

    /**
     * @param bool $original
     * @return int[]
     */
    public function getRelationId(bool $original = false): array
    {
        return $this->getFieldFromArray(Constants::FIELD_RELATION_ID, $original, []);
    }

    public function setRelationId(array $ids): void
    {
        $this->fields[Constants::FIELD_RELATION_ID] = $ids;
    }

    public function getRelationModel(bool $original = false): string
    {
        return $this->getFieldFromArray(Constants::FIELD_RELATION_MODEL, $original, '');
    }

    public function setRelationModel(string $model): void
    {
        $this->fields[Constants::FIELD_RELATION_MODEL] = $model;
    }

    /**
     * @param bool $original
     * @return string[]
     */
    public function getWith(bool $original = false): array
    {
        return $this->getFieldFromArray(Constants::FIELD_WITH, $original, []);
    }

    public function setWith(array $with): void
    {
        $this->fields[Constants::FIELD_WITH] = $with;
    }

    /**
     * @param string $name
     * @param bool $original
     * @param mixed $default
     * @return mixed
     */
    private function getFieldFromArray(string $name, bool $original, $default)
    {
        $arr = $original
            ? $this->original
            : $this->fields;
        return isset($arr[$name])
            ? $arr[$name]
            : $default;
    }

    private function getFieldFromSubarray(string $sourceArray, string $name, bool $original, $default)
    {
        $arr = $original
            ? $this->original
            : $this->fields;
        return (isset($arr[$sourceArray]) && isset($arr[$sourceArray][$name]))
            ? $arr[$sourceArray][$name]
            : $default;
    }

    /**
     * @param array $fields
     * @throws Exception
     */
    public function setQuery(array $fields): void
    {
        foreach ($fields as $key => $value) {
            if (!is_array($value)) {
                $value = trim($value);
            }
            if ($value === '') {
                continue;
            }
            switch ($key) {
                case Constants::FIELD_FULL_SEARCH:
                case Constants::FIELD_RELATION_MODEL:
                    break;
                case Constants::FIELD_ORDER:
                case Constants::FIELD_SUBSTRING_SEARCH:
                case Constants::FIELD_RANGE_FROM:
                case Constants::FIELD_RANGE_TO:
                case Constants::FIELD_RELATION_ID:
                    $value = $this->extractSearch($value);
                    break;
                case Constants::FIELD_WITH:
                    $value = array_unique(array_filter($this->extractSearch($value)));
                    break;
                case Constants::FIELD_LIMIT_FROM:
                case Constants::FIELD_LIMIT_COUNT:
                    $value = (int)$value;
                    $value = ($value < 0)
                        ? 0
                        : $value;
                    break;
                default:
                    if ((substr($value, 0, 1) == '[') && (substr($value, strlen($value) - 1) == ']')) {
                        $this->fields[Constants::FIELD_FIELDS][$key]
                            = $this->original[Constants::FIELD_FIELDS][$key]
                            = json_decode($value, true);
                        if (json_last_error()) {
                            throw new Exception(sprintf('Wrong JSON for field %s: %s', $key, json_last_error_msg()));
                        }
                    } else {
                        $this->fields[Constants::FIELD_FIELDS][$key]
                            = $this->original[Constants::FIELD_FIELDS][$key]
                            = $value;
                    }
                    continue 2;
            }
            $this->original[$key] = $this->fields[$key] = $value;
        }
    }

    /**
     * @param string $str
     * @return array
     * @throws Exception
     */
    private function extractSearch(string $str): array
    {
        $result = [];
        $data = json_decode($str, TRUE);
        if (json_last_error()) {
            throw new Exception('Unable to parse JSON: ' . json_last_error_msg() . ' => ' . $str);
        }
        if (!is_array($data)) {
            $data = [$data];
        }
        foreach ($data as $key => $value) {
            $result[$key] = $value;
        }
        return $result;
    }

}
