<?php

namespace User\Domain\Task\ByPersonnel;

use User\Domain\Model\Personnel;

class ChangeName implements PersonnelTask
{
    
    /**
     * 
     * @param Personnel $personnel
     * @param string $payload new name
     * @return void
     */
    public function executeByPersonnel(Personnel $personnel, $payload): void
    {
        $personnel->changeName($payload);
    }
}
