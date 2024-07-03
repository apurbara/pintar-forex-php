<?php

namespace Company\Domain\Task\SalesRank;

use Company\Domain\Model\SalesRank;

interface SalesRankRepository
{

    public function nextIdentity(): string;

    public function add(SalesRank $salesRank): void;

    public function ofId(string $id): SalesRank;

    /**
     * 
     * @return SalesRank[]
     */
    public function allActive(): array;

    //
    public function aSalesRank(string $id);

    public function salesRankList(array $paginationSchema);
}
