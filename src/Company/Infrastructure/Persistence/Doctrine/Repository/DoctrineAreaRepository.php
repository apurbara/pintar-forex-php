<?php

namespace Company\Infrastructure\Persistence\Doctrine\Repository;

use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Task\InCompany\AreaStructure\Area\AreaRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineEntityRepository;
use Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrinePaginationListCategory;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;

class DoctrineAreaRepository extends DoctrineEntityRepository implements AreaRepository
{

    public function add(Area $area): void
    {
        $this->persist($area);
    }

    public function isAreaRootNameAvailable(string $name): bool
    {
        $filters = [
            new Filter($name, 'Area.name'),
        ];
        return empty($this->fetchOneBy($filters));
    }

    public function isChildAreaNameAvailable(string $parentAreaId, string $name): bool
    {
        $filters = [
            new Filter($name, 'Area.name'),
            new Filter($parentAreaId, 'Area.Area_idOfParent'),
        ];
        return empty($this->fetchOneBy($filters));
    }

    public function ofId(string $id): Area
    {
        return $this->findOneByIdOrDie($id);
    }

    public function viewAreaDetail(string $id): array
    {
        return $this->fetchOneByIdOrDie($id);
    }

    public function viewAreaList(array $paginationSchema): array
    {
        $doctrinePaginationListCategory = DoctrinePaginationListCategory::fromSchema($paginationSchema);
        return $this->fetchPaginationList($doctrinePaginationListCategory);
    }

    public function viewAllAreaList(array $searchSchema): array
    {
        $doctrineAllListCategory = \Resources\Infrastructure\Persistence\Doctrine\Repository\DoctrineAllListCategory::fromSchema($searchSchema);
        return $this->fetchAllList($doctrineAllListCategory);
    }
}
