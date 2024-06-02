<?php

namespace User\Application\Service\Sales;

use User\Domain\Model\Sales;

interface SalesRepository
{

    public function ofId(string $id): Sales;

    public function update(): void;
}
