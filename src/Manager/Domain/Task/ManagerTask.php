<?php

namespace Manager\Domain\Task;

use Manager\Domain\Model\Personnel\Manager;

interface ManagerTask
{

    public function executeByManager(Manager $manager, $payload): void;
}
