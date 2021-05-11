<?php

namespace EgalFramework\APIContainer\Models;

/**
 * Class AbstractModel
 * @package EgalFramework\APIContainer\Models
 */
class AbstractModel
{

    /** @var array */
    protected array $skipFields = [];

    /**
     * AbstractModel constructor.
     */
    public function __construct()
    {
        $this->skipFields[] = 'skipFields';
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        $obj = get_object_vars($this);
        foreach ($this->skipFields as $field) {
            unset($obj[$field]);
        }
        return json_encode($obj, JSON_PRETTY_PRINT);
    }

    public function fromString(string $str)
    {
        $data = json_decode($str, TRUE);
        foreach (array_keys(get_object_vars($this)) as $field) {
            if (in_array($field, $this->skipFields) || empty($data[$field])) {
                continue;
            }
            $this->{$field} = $data[$field];
        }
    }

}
