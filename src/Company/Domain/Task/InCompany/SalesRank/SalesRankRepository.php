<?php

namespace Company\Domain\Task\InCompany\SalesRank;

use Company\Domain\Model\SalesRank;

interface SalesRankRepository
{

    /**
     * 
     * @return SalesRank[]
     */
    public function allActive(): array;
}
