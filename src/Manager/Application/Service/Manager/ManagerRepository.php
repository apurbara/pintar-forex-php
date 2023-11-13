<?php

namespace Manager\Application\Service\Manager;

use Manager\Domain\Model\Personnel\Manager;

interface ManagerRepository
{

    public function aManagerBelongsToPersonnel(string $personnelId, string $managerId): Manager;
    
    public function update(): void;
}
