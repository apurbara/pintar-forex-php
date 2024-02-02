<?php

namespace Company\Domain\Task\InCompany\AreaStructure\Area;

use Company\Domain\Model\AreaStructure\Area;

interface AreaRepository
{

    public function nextIdentity(): string;

    public function add(Area $area): void;

    public function ofId(string $id): Area;

    public function isAreaRootNameAvailable(string $name): bool;

    public function isChildAreaNameAvailable(string $parentAreaId, string $name): bool;

    public function viewAreaList(array $paginationSchema): array;

    public function viewAllAreaList(array $searchSchema): array;

    public function viewAreaDetail(string $id): array;
}
