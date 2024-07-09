<?php

namespace Company\Domain\Task\Customer;

use Company\Domain\Model\AdminTaskInCompany;
use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerData;
use Company\Domain\Model\ManagerTaskInCompany;
use Company\Domain\Task\City\CityRepository;
use Resources\Exception\RegularException;

class AddCustomer implements AdminTaskInCompany, ManagerTaskInCompany
{

    public function __construct(
            protected CustomerRepository $customerRepository, protected CityRepository $cityRepository)
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
        if (isset($payload->cityId)) {
            $city = $this->cityRepository->ofId($payload->cityId);
        }

        $customer = new Customer($city ?? null, $payload->id, $payload);
        $this->customerRepository->add($customer);
    }
}
