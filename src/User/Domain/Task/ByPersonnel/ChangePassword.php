<?php

namespace User\Domain\Task\ByPersonnel;

use SharedContext\Domain\ValueObject\ChangeUserPasswordData;
use User\Domain\Model\Personnel;

class ChangePassword implements PersonnelTask
{
    
    /**
     * 
     * @param Personnel $personnel
     * @param ChangeUserPasswordData $payload
     * @return void
     */
    public function executeByPersonnel(Personnel $personnel, $payload): void
    {
        $personnel->changePassword($payload);
    }
}
