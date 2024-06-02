<?php

namespace Sales\Application\Service\Sales;

use Sales\Domain\Model\Sales;

interface SalesRepository
{

    public function ofId(string $id): Sales;

    public function update(): void;
}
