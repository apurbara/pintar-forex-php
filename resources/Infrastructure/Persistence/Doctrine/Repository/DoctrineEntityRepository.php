<?php

namespace Resources\Infrastructure\Persistence\Doctrine\Repository;
//use Resources\Infrastructure\Persistence\Doctrine\Attribute\QueryEntity;


use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Table;
use ReflectionClass;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\GraphqlQueryableRepository;
use Resources\Infrastructure\Persistence\Doctrine\Attribute\QueryEntity;
use Resources\Infrastructure\Persistence\Doctrine\DoctrineEntityToSqlFieldsMapper;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Resources\ReflectionHelper;
use Resources\Uuid;

abstract class DoctrineEntityRepository extends EntityRepository implements GraphqlQueryableRepository
{

//    use DoctrineRepositoryTrait;

    public function nextIdentity(): string
    {
        return Uuid::generateUuid4();
    }

    public function update(): void
    {
        $this->getEntityManager()->flush();
    }

    protected function persist($entity): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
    }

    //
    protected function findOneByIdOrDie(string $id)
    {
        $entity = $this->findOneBy([
            "id" => $id,
        ]);
        if (empty($entity)) {
            $shortEntityName = (new ReflectionClass($this->getEntityName()))->getShortName();
            $errorDetail = "not found: '{$shortEntityName}' not found";
            throw RegularException::notFound($errorDetail);
        }
        return $entity;
    }

//
//    protected readonly ?string $queryEntityMetadata;

    protected function getQueryEntityMetadata(): string
    {
        $entityName = $this->getEntityName();
        return ReflectionHelper::getAttributeArgument(new \ReflectionClass($entityName), QueryEntity::class, 'targetEntity') ?? $entityName;
    }

    protected function getTableName(): string
    {
        $entityReflection = new ReflectionClass($this->getEntityName());
        return ReflectionHelper::getAttributeArgument($entityReflection, Table::class, 'name') ?? $entityReflection->getShortName();
    }

    protected function dbalQueryBuilder(): QueryBuilder
    {
        return $this->getEntityManager()->getConnection()->createQueryBuilder();
    }

    protected function createCoreQueryBuilder(): QueryBuilder
    {
        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $sqlMap = DoctrineEntityToSqlFieldsMapper::mapFields($this->getQueryEntityMetadata());
        $qb->from($this->getTableName());
        foreach ($sqlMap['selectFields'] as $selectField) {
            $qb->addSelect($selectField);
        }
        foreach ($sqlMap['joins'] as $join) {
            $qb->innerJoin($join['from'], $join['to'], $join['to'], $join['condition']);
        }
        return $qb;
    }

    //
    protected function fetchPaginationList(DoctrinePaginationListCategory $doctrinePaginationListCategory): array
    {
        $qb = $this->createCoreQueryBuilder();
        return $doctrinePaginationListCategory->paginateResult($qb, $this->getTableName());
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
    protected function fetchOneBy($filters): ?array
    {
        $qb = $this->createCoreQueryBuilder();
        $qb->setMaxResults(1);
        foreach ($filters as $filter) {
            $filter->applyToQuery($qb);
        }
        return $qb->executeQuery()->fetchAssociative() ?: null;
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
//
//    public function fetchOneById(string $id): ?array
//    {
//        return $this->fetchOneBy([new Filter($id, "{$this->getTableName()}.id")]);
//    }

    //
    public function queryOneById(string $id): ?array
    {
        return $this->fetchOneBy([new Filter($id, "{$this->getTableName()}.id")]);
//        return $this->fetchOneById($id);
    }
    
    public function queryOneBy(array $filters): ?array
    {
        return $this->fetchOneBy($filters);
    }

    public function queryAllList(array $searchSchema): array
    {
        $doctrineAllListCategory = DoctrineAllListCategory::fromSchema($searchSchema);
        return $this->fetchAllList($doctrineAllListCategory);
    }

    public function queryPaginationList(array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema);
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }
}
