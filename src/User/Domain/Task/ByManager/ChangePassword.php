<?php

namespace User\Domain\Task\ByManager;

use SharedContext\Domain\ValueObject\ChangeUserPasswordData;
use User\Domain\Model\Manager;

class ChangePassword implements ManagerTask
{

    /**
     * 
     * @param Manager $manager
     * @param ChangeUserPasswordData $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $manager->changePassword($payload);
    }
}
