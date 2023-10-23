<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\AreaStructure;
use Company\Domain\Task\InCompany\AreaStructure\AreaStructureRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;

class DoctrineAreaStructureRepository extends DoctrineEntityRepository implements AreaStructureRepository
{
    
    public function add(AreaStructure $areaStructure): void
    {
        $this->persist($areaStructure);
    }

    public function viewAreaStructureDetail(string $id): array
    {
        return $this->fetchOneByIdOrDie($id);
    }

    public function viewAreaStructureList(array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema);
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }

    public function isNameAvailable(string $name): bool
    {
        $filters = [
            new Filter($name, 'AreaStructure.name'),
        ];
        return empty($this->fetchOneBy($filters));
    }

    public function ofId(string $id): AreaStructure
    {
        return $this->findOneByIdOrDie($id);
    }

}
