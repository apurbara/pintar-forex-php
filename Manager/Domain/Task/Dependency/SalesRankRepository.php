<?php

namespace Manager\Domain\Task\Dependency;

use Manager\Domain\DependencyModel\SalesRank;

interface SalesRankRepository
{
    /**
     * 
     * @return SalesRank[]
     */
    public function allActive(): array;
}
