<?php

namespace Company\Domain\Service;

use Company\Domain\Model\Manager\Sales;

interface SalesRepository
{

public function findLeastOccupiedFactFinderBelongsToManager(string $managerId): ?Sales;

    public function findLeastOccupiedStrikerBelongsToManager(string $managerId): ?Sales;
}
