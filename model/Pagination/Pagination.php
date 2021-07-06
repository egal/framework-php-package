<?php

namespace Egal\Model\Pagination;

class Pagination
{

    private ?int $perPage = null;
    private ?int $page = null;

    public static function fromArray(array $array): Pagination
    {
        $result = new Pagination();
        !isset($array['per_page']) ?: $result->perPage = $array['per_page'];
        !isset($array['page']) ?: $result->page = $array['page'];
        return $result;
    }

    /**
     * @return int|null
     */
    public function getPerPage(): ?int
    {
        return $this->perPage;
    }

    /**
     * @return int|null
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * @param int|null $perPage
     * @return Pagination
     */
    public function setPerPage(?int $perPage): Pagination
    {
        $this->perPage = $perPage;
        return $this;
    }

    /**
     * @param int|null $page
     * @return Pagination
     */
    public function setPage(?int $page): Pagination
    {
        $this->page = $page;
        return $this;
    }

}
