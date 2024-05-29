<?php

namespace Sales\Domain\Task\BySales\CustomerAssignment;

use Resources\Event\Dispatcher;
use Resources\Exception\RegularException;
use Sales\Domain\DependencyModel\AreaStructure\Area\Customer;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Task\BySales\SalesTask;
use Sales\Domain\Task\Dependency\AreaRepository;
use Sales\Domain\Task\Dependency\CustomerJourneyRepository;
use Sales\Domain\Task\Dependency\CustomerRepository;

class RegisterNewCustomerTask implements SalesTask
{

    public function __construct(
            protected CustomerAssignmentRepository $customerAssignmentRepository,
            protected AreaRepository $areaRepository, protected CustomerRepository $customerRepository,
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

        $area = $this->areaRepository->ofId($payload->areaId);
        $customer = new Customer($area, $payload->customerData);
        $customerJourney = $this->customerJourneyRepository->anInitialCustomerJourney();
        $customerAssignment = new CustomerAssignment($sales, $customer, $customerJourney, $payload->id);
        $this->customerAssignmentRepository->add($customerAssignment);

        $this->dispatcher->dispatchEventContainer($customerAssignment);
    }
}
