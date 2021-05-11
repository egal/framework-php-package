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
     * @param int|null $perPage
     */
    public function setPerPage(?int $perPage): void
    {
        $this->perPage = $perPage;
    }

    /**
     * @return int|null
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * @param int|null $page
     */
    public function setPage(?int $page): void
    {
        $this->page = $page;
    }


}
