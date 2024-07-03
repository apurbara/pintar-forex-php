<?php

namespace Manager\Domain\Task;

use Manager\Domain\Model\Manager;

interface ManagerRepository
{

    public function ofId(string $id): Manager;

    public function update(): void;
}
