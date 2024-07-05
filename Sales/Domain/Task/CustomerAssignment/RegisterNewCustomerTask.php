<?php

namespace Sales\Domain\Task\CustomerAssignment;

use Resources\Event\Dispatcher;
use Resources\Exception\RegularException;
use Sales\Domain\DependencyModel\Customer;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Task\Dependency\CityRepository;
use Sales\Domain\Task\Dependency\CustomerJourneyRepository;
use Sales\Domain\Task\Dependency\CustomerRepository;
use Sales\Domain\Task\SalesTask;

class RegisterNewCustomerTask implements SalesTask
{

    public function __construct(
            protected CustomerAssignmentRepository $customerAssignmentRepository,
            protected CityRepository $cityRepository, protected CustomerRepository $customerRepository,
            protected CustomerJourneyRepository $customerJourneyRepository, protected Dispatcher $dispatcher,
    )
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param RegisterNewCustomerPayload $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $payload->setId($this->customerAssignmentRepository->nextIdentity());
        $payload->customerData->setId($this->customerRepository->nextIdentity());
        
        if (!$this->customerRepository->isPhoneAvailable($payload->customerData->phone)) {
            throw RegularException::conflict('phone already registered');
        }

        $city = $this->cityRepository->ofId($payload->customerData->cityId);
        $customer = new Customer($city, $payload->customerData->id, $payload->customerData);
        $customerJourney = $this->customerJourneyRepository->anInitialCustomerJourney();
        $customerAssignment = new CustomerAssignment($sales, $customer, $customerJourney, $payload->id);
        $this->customerAssignmentRepository->add($customerAssignment);

        $this->dispatcher->dispatchEventContainer($customerAssignment);
    }
}
