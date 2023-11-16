<?php

namespace Resources\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use ReflectionClass;
use Resources\Exception\RegularException;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;

trait DoctrineRepositoryTrait
{

    abstract protected function getTableName(): string;

    abstract protected function createCoreQueryBuilder(): QueryBuilder;

    //
    protected function fetchPaginationList(DoctrinePaginationListCategory $doctrinePaginationListCategory): array
    {
        $qb = $this->createCoreQueryBuilder();
        return $doctrinePaginationListCategory->paginateResult($qb);
    }

    protected function fetchAllList(DoctrineAllListCategory $doctrineAllListCategory): array
    {
        $qb = $this->createCoreQueryBuilder();
        return $doctrineAllListCategory->fetchResult($qb);
    }

    /**
     * 
     * @param Filter[] $filter
     * @return array
     */
    protected function fetchOneBy($filters): array|bool
    {
        $qb = $this->createCoreQueryBuilder();
        $qb->setMaxResults(1);
        foreach ($filters as $filter) {
            $filter->applyToQuery($qb);
        }
        return $qb->executeQuery()->fetchAssociative();
    }

    /**
     * 
     * @param Filter[] $filter
     * @return array
     * @throws RegularException
     */
    protected function fetchOneOrDie($filters): array
    {
        $result = $this->fetchOneBy($filters);
        if (empty($result)) {
            $shortEntityName = (new ReflectionClass($this->getEntityName()))->getShortName();
            $errorDetail = "not found: '{$shortEntityName}' not found";
            throw RegularException::notFound($errorDetail);
        }
        return $result;
    }

    public function fetchOneByIdOrDie(string $id): array
    {
        return $this->fetchOneOrDie([new Filter($id, "{$this->getTableName()}.id")]);
    }

    public function fetchOneById(?string $id): ?array
    {
        return $this->fetchOneBy([new Filter($id, "{$this->getTableName()}.id")]);
    }

}
