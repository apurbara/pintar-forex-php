<?php

namespace Company\Domain\Service;

use Company\Domain\Model\Manager\Sales;

class SalesFinderService
{
    
    public function __construct(protected SalesRepository $salesRepository)
    {
    }

    public function findLeastOccupiedFactFinderBelongsToManager(string $managerId): ?Sales
    {
        return $this->salesRepository->findLeastOccupiedFactFinderBelongsToManager($managerId);
    }

    public function findLeastOccupiedStrikerBelongsToManager(string $managerId): ?Sales
    {
        return $this->salesRepository->findLeastOccupiedStrikerBelongsToManager($managerId);
    }
}
