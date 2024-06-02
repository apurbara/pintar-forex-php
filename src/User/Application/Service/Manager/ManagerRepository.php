<?php

namespace User\Application\Service\Manager;

use User\Domain\Model\Manager;

interface ManagerRepository
{

    public function ofId(string $id): Manager;

    public function update(): void;
}
