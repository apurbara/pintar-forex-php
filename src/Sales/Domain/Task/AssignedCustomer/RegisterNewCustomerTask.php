<?php

namespace Sales\Domain\Task\AssignedCustomer;

use Resources\Event\Dispatcher;
use Resources\Exception\RegularException;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Task\Area\AreaRepository;
use Sales\Domain\Task\Customer\CustomerRepository;
use Sales\Domain\Task\SalesTask;

class RegisterNewCustomerTask implements SalesTask
{

    public function __construct(
            protected AssignedCustomerRepository $assignedCustomerRepository,
            protected AreaRepository $areaRepository,
            protected CustomerRepository $customerRepository,
            protected Dispatcher $dispatcher,
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
        $payload->setId($this->assignedCustomerRepository->nextIdentity());
        $payload->customerData->setId($this->customerRepository->nextIdentity());
        
        if (!$this->customerRepository->isEmailAvailable($payload->customerData->email)) {
            throw RegularException::conflict('email already registered');
        }
        
        $customerArea = $this->areaRepository->ofId($payload->areaId);
        $customerArea->assertAccessible();
        
        $assignedCustomer = $sales->registerNewCustomer($customerArea, $payload->id, $payload->customerData);
        
        $this->assignedCustomerRepository->add($assignedCustomer);
        $this->dispatcher->dispatchEventContainer($assignedCustomer);
        
    }
}
