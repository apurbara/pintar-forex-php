<?php

namespace Company\Domain\Task\InCompany\Customer;

use Company\Domain\Model\AreaStructure\Area\Customer;
use Company\Domain\Model\AreaStructure\Area\CustomerData;
use Company\Domain\Model\PersonnelHavingManagerAssignmentTaskInCompany;
use Company\Domain\Task\InCompany\AreaStructure\Area\AreaRepository;
use Resources\Exception\RegularException;

class AddCustomer implements PersonnelHavingManagerAssignmentTaskInCompany
{

    public function __construct(
            protected CustomerRepository $customerRepository, protected AreaRepository $areaRepository)
    {
        
    }

    /**
     * 
     * @param CustomerData $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        if (!$this->customerRepository->isPhoneAvailable($payload->phone)) {
            throw RegularException::conflict('customer phone already registered');
        }
        
        $payload->setId($this->customerRepository->nextIdentity());
        if (isset($payload->areaId)) {
            $area = $this->areaRepository->ofId($payload->areaId);
        }
        
        $customer = new Customer($area ?? null, $payload->id, $payload);
        $this->customerRepository->add($customer);
    }
}
