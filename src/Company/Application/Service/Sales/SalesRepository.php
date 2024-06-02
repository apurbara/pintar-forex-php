<?php

namespace Company\Application\Service\Sales;

use Company\Domain\Model\Sales;

interface SalesRepository
{

    public function ofId(string $id): Sales;

    public function update(): void;
}
