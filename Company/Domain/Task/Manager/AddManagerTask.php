<?php

namespace Company\Domain\Task\Manager;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Manager;
use Company\Domain\Model\ManagerData;
use Resources\Exception\RegularException;

class AddManagerTask implements AdminTaskInCompany
{
    public function __construct(protected ManagerRepository $managerRepository)
    {
    }

    /**
     * 
     * @param ManagerData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        if (!$this->managerRepository->isEmailAvailable($payload->accountInfoData->email)) {
            throw RegularException::conflict('email already registered');
        }
        $payload->setId($this->managerRepository->nextIdentity());
        $manager = new Manager($payload->id, $payload);
        $this->managerRepository->add($manager);
    }
}
