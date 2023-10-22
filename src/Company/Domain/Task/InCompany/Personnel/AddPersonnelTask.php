<?php

namespace Company\Domain\Task\InCompany\Personnel;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\PersonnelData;
use Resources\Exception\RegularException;

class AddPersonnelTask implements AdminTaskInCompany
{
    public function __construct(protected PersonnelRepository $personnelRepository)
    {
    }

    /**
     * 
     * @param PersonnelData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        if (!$this->personnelRepository->isEmailAvailable($payload->accountInfoData->email)) {
            throw RegularException::conflict('email already registered');
        }
        $payload->setId($this->personnelRepository->nextIdentity());
        $personnel = new Personnel($payload);
        $this->personnelRepository->add($personnel);
    }
}
