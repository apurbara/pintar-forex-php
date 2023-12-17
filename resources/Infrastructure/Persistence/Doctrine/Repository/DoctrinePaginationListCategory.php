<?php

namespace Resources\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\Doctrine\Repository\PaginationListCategory\CursorLimit;
use Resources\Infrastructure\Persistence\Doctrine\Repository\PaginationListCategory\OffsetLimit;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\KeywordSearch;

class DoctrinePaginationListCategory
{

    protected KeywordSearch $keywordSearch;

    /**
     * 
     * @var Filter[]
     */
    protected $filters;
    protected PageLimitInterface $pageLimit;

    public function __construct(KeywordSearch $keywordSearch, array $filters, PageLimitInterface $pageLimit)
    {
        $this->keywordSearch = $keywordSearch;
        $this->filters = $filters;
        $this->pageLimit = $pageLimit;
    }

    public static function fromSchema(array $schema): static
    {
        $filters = [];
        foreach ($schema['filters'] as $filter) {
            $filters[] = Filter::fromSchema($filter);
        };
        if (!empty($schema['cursorLimit'])) {
            $pageLimit = CursorLimit::fromSchema($schema['cursorLimit']);
        } else {
            $pageLimit = !empty($schema['offsetLimit']) ? OffsetLimit::fromSchema($schema['offsetLimit']) : CursorLimit::fromSchema();
        }

        return new static(KeywordSearch::fromSchema($schema['keywordSearch'] ?? []), $filters, $pageLimit);
    }

    public function paginateResult(QueryBuilder $qb, string $tableName): array
    {
        $this->keywordSearch->applyToQuery($qb);
        foreach ($this->filters as $filter) {
            $filter->applyToQuery($qb);
        }
        return $this->pageLimit->paginateResult($qb, $tableName);
    }

    //
    public function addFilter(Filter $filter): self
    {
        $this->filters[] = $filter;
        return $this;
    }
}
