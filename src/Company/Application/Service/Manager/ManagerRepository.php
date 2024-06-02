<?php

namespace Company\Application\Service\Manager;

use Company\Domain\Model\Manager;

interface ManagerRepository
{

    public function ofId(string $id): Manager;

    public function update(): void;
}
