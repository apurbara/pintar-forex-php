<?php

namespace User\Domain\Task\ByManager;

use User\Domain\Model\Manager;

interface ManagerTask
{

    public function executeByManager(Manager $manager, $payload): void;
}
