<?php

namespace Resources\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\Table;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Reflector;
use Resources\Attributes\Composed;
use Resources\Attributes\ExcludeFromFetch;
use Resources\Exception\RegularException;
use Resources\Uuid;

abstract class DoctrineEntityRepository extends EntityRepository
{

    use DoctrineRepositoryTrait;

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

    protected function initializeEntityResources(): void
    {
        $this->columns = [];
        $this->compositionEntities = [];
        $entityReflection = new ReflectionClass($this->getEntityName());
        $this->tableName = $this->getAttribute($entityReflection, Table::class)?->getArguments()['name'] ?? $entityReflection->getShortName();
        $this->setColumnsAndCompositionRelation($entityReflection, $this->tableName);
    }

    private function setColumnsAndCompositionRelation(
            \ReflectionClass $classReflection, string $table, ?string $columnPrefix = null, bool $excludeId = false): void
    {
        foreach ($this->iterateReflectionPropertiesOfClass($classReflection) as $propertyReflection) {
            if ($excludeId && $propertyReflection->getName() === 'id') {
                continue;
            }
            
            $isExcluded = $this->getAttribute($propertyReflection, ExcludeFromFetch::class);
            if ($isExcluded) {
                continue;
            }

            $columnAttributeReflection = $this->getAttribute($propertyReflection, Column::class);
            if ($columnAttributeReflection) {
                $colName = $columnPrefix . $propertyReflection->getName();
                $this->columns["{$table}.{$colName}"] = $colName;
                continue;
            }

            $embeddedAttributeReflection = $this->getAttribute($propertyReflection, Embedded::class);
            if ($embeddedAttributeReflection) {
                $this->setColumnsAndCompositionRelation(
                        new \ReflectionClass(
                                $embeddedAttributeReflection->getArguments()['class']), 
                                $table,
                                $embeddedAttributeReflection->getArguments()['columnPrefix'] ?? null
                        );
                continue;
            }

            $composedAttributeReflection = $this->getAttribute($propertyReflection, Composed::class);
            if ($composedAttributeReflection) {
                $composedClassReflection = new \ReflectionClass($composedAttributeReflection->getArguments()['class']);
                $composedTableName = $this->getAttribute($composedClassReflection, Table::class)?->getArguments()['name'] ?? $composedClassReflection->getShortName();
//                $this->compositionEntities[$table] = $composedTableName;
                $joinColName = $this->getAttribute($propertyReflection, JoinColumn::class)->getArguments()['name'];
                $this->compositionEntities[] = [
                    'from' => $table,
                    'join' => $composedTableName,
                    'condition' => "{$table}.{$joinColName} = {$composedTableName}.id",
                ];
                $this->setColumnsAndCompositionRelation($composedClassReflection, $composedTableName, null, true);
                continue;
            }

            $joinColumnAttributeReflection = $this->getAttribute($propertyReflection, JoinColumn::class);
            if ($joinColumnAttributeReflection) {
                $refColumnName = $joinColumnAttributeReflection->getArguments()['name'];
                $this->columns["{$table}.{$refColumnName}"] = $refColumnName;
                continue;
            }
        }
    }

    /**
     * 
     * @param string $classMetadata
     * @return ReflectionProperty[]
     */
    private static function iterateReflectionPropertiesOfClass(\ReflectionClass $classReflection)
    {
        return $classReflection->getProperties();
    }

    private static function getAttribute(Reflector $reflection, string $attributeMetadata): ?ReflectionAttribute
    {
        $attributesReflection = $reflection->getAttributes($attributeMetadata, ReflectionAttribute::IS_INSTANCEOF);
        return $attributesReflection[0] ?? null;
    }

    /**
     * 
     * @var [
     *  'fromTabel' => 'joinTable',
     * ]
     */
    protected ?array $compositionEntities = null;

    protected function getCompositionEntities(): array
    {
        if (is_null($this->compositionEntities)) {
            $this->initializeEntityResources();
        }
        return $this->compositionEntities;
    }

    /**
     * 
     * @var [
     *  'Table.colName' => 'alias',
     * ]
     */
    protected ?array $columns = null;

    protected function getSelectColumns(): array
    {
        if (is_null($this->columns)) {
            $this->initializeEntityResources();
        }
        return $this->columns;
    }

    protected ?string $tableName = null;

    protected function getTableName(): string
    {
        if (is_null($this->tableName)) {
            $this->initializeEntityResources();
        }
        return $this->tableName;
    }

//
    protected function dbalQueryBuilder(): QueryBuilder
    {
        return $this->getEntityManager()->getConnection()->createQueryBuilder();
    }

    protected function createCoreQueryBuilder(): QueryBuilder
    {
        $qb = $this->getEntityManager()->getConnection()->createQueryBuilder();
        foreach ($this->getSelectColumns() as $tableColumnName => $alias) {
            $qb->addSelect("$tableColumnName AS $alias");
        }
        $qb->from($this->getTableName());
        foreach ($this->getCompositionEntities() as $composition) {
            $qb->innerJoin($composition['from'], $composition['join'], $composition['join'], $composition['condition']);
        }
//        foreach ($this->getCompositionEntities() as $fromAlias => $join) {
//            $condition = "{$fromAlias}.{$join}_id = {$join}.id";
//            $qb->innerJoin($fromAlias, $join, $join, $condition);
//        }
        return $qb;
    }
}
