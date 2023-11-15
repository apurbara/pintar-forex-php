<?php

namespace Sales\Domain\Task\SalesActivity;

use Sales\Domain\Model\SalesActivity;

interface SalesActivityRepository
{

    public function ofId(string $id): SalesActivity;
    
    public function anInitialSalesActivity(): ?SalesActivity;
}
