<?php

namespace Nette\Database\Table;

/**
 * @phpstan-implements \Iterator<string, ActiveRow>
 * @phpstan-implements \ArrayAccess<string, \Nette\Database\IRow>
 */
class Selection implements \Iterator, \ArrayAccess
{
    /**
     * @phpstan-param positive-int|0|null $limit
     * @phpstan-param positive-int|0|null $offset
     * @return static
     */
    public function limit(?int $limit, int $offset = null)
    {
    }

    /**
     * @phpstan-param positive-int|0 $page
     * @phpstan-param positive-int|0 $itemsPerPage
     * @param int $numOfPages [optional]
     * @return static
     */
    public function page(int $page, int $itemsPerPage, &$numOfPages = null)
    {
    }

    /**
     * Adds where condition, more calls appends with AND.
     * @param mixed $condition
     * @param mixed $params
     * @return static
     */
    public function where($condition, ...$params)
    {
    }
}