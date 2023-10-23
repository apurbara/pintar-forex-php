<?php

namespace Company\Domain\Task\InCompany\AreaStructure;

use Company\Domain\Model\AreaStructure;

interface AreaStructureRepository
{

    public function nextIdentity(): string;

    public function add(AreaStructure $areaStructure): void;

    public function ofId(string $id): AreaStructure;

    public function isNameAvailable(string $name): bool;

    public function viewAreaStructureList(array $paginationSchema): array;

    public function viewAreaStructureDetail(string $id): array;
}
