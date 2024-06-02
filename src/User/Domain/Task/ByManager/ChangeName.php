<?php

namespace User\Domain\Task\ByManager;

use User\Domain\Model\Manager;

class ChangeName implements ManagerTask
{
    /**
     * 
     * @param Manager $manager
     * @param string $payload new name
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $manager->changeName($payload);
    }
}
