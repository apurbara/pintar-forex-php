<?php

namespace Resources\Infrastructure\Persistence\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

abstract class DoctrineQueryRepository
{

    use DoctrineRepositoryTrait;

    public function __construct(protected EntityManagerInterface $em)
    {
        
    }

    /**
     * 
     * @var array [
     *  'Table.colName' => 'alias',
     * ]
     */
    protected array $columns = [];

    protected function getSelectColumn(): string
    {
        return empty($this->columns) ? "*" : implode(', ',
                        array_map(fn($value, $key) => "{$key}.{$value} AS $value", $this->columns,
                                array_keys($this->columns)));
    }

    /**
     * 
     * @var array [
     *  'fromTable' => 'joinTable',
     * ]
     */
    protected array $compositionEntities = [];

    protected function getCompositionEntities(): array
    {
        return $this->compositionEntities;
    }

    //
    protected function createDBALQueryBuilder(): QueryBuilder
    {
        $qb = $this->em->getConnection()->createQueryBuilder();
        $qb->select($this->getSelectColumns())
                ->from($this->getTableName());
        foreach ($this->getCompositionEntities() as $fromAlias => $join) {
            $condition = "{$fromAlias}.{$join}_id = {$join}.id";
            $qb->innerJoin($fromAlias, $join, $join, $condition);
        }
        return $qb;
    }
}
