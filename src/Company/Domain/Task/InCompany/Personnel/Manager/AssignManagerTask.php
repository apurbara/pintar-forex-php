<?php

namespace Company\Domain\Task\InCompany\Personnel\Manager;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Personnel\ManagerData;
use Company\Domain\Task\InCompany\Personnel\PersonnelRepository;

class AssignManagerTask implements AdminTaskInCompany
{

    public function __construct(
            protected ManagerRepository $managerRepository, protected PersonnelRepository $personnelRepository)
    {
        
    }

    /**
     * 
     * @param ManagerData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $payload->setId($this->managerRepository->nextIdentity());
        $manager = $this->personnelRepository->ofId($payload->personnelId)
                ->assignAsManager($payload);
        $this->managerRepository->add($manager);
    }
}
