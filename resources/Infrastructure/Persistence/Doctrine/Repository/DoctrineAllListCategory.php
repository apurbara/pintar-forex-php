<?php

namespace Resources\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Resources\Infrastructure\Persistence\AllListCategoryInterface;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\KeywordSearch;

class DoctrineAllListCategory
{

    protected KeywordSearch $keywordSearch;

    /**
     * 
     * @var Filter[]
     */
    protected $filters;

    public function __construct(KeywordSearch $keywordSearch, array $filters)
    {
        $this->keywordSearch = $keywordSearch;
        $this->filters = $filters;
    }

    public static function fromSchema(array $schema = []): static
    {
        $filters = [];
        foreach ($schema['filters'] ?? [] as $filter) {
            $filters = Filter::fromSchema($filter);
        };
        return new static(KeywordSearch::fromSchema($schema['keywordSearch'] ?? []), $filters);
    }

    public function fetchResult(QueryBuilder $qb): array
    {
        $this->keywordSearch->applyToQuery($qb);
        foreach ($this->filters as $filter) {
            $filter->applyToQuery($qb);
        }
        return $qb->executeQuery()->fetchAllAssociative();
    }
    
    //
    public function addFilter(Filter $filter): self
    {
        $this->filters[] = $filter;
        return $this;
    }
}
