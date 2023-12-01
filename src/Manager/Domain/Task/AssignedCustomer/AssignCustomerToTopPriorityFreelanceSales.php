<?php

namespace Manager\Domain\Task\AssignedCustomer;

use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomerData;
use Manager\Domain\Service\CustomerAssignmentPriorityCalculatorService;
use Manager\Domain\Task\Customer\CustomerRepository;
use Manager\Domain\Task\CustomerJourney\CustomerJourneyRepository;
use Manager\Domain\Task\ManagerTask;
use Resources\Event\Dispatcher;

class AssignCustomerToTopPriorityFreelanceSales implements ManagerTask
{

    public function __construct(
            protected AssignedCustomerRepository $assignedCustomerRepository,
            protected CustomerRepository $customerRepository,
            protected CustomerJourneyRepository $customerJourneyRepository,
            protected CustomerAssignmentPriorityCalculatorService $assignmentPriorityCalculator,
            protected Dispatcher $dispatcher)
    {
        
    }

    /**
     * 
     * @param Manager $manager
     * @param AssignedCustomerData $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $manager->registerActiveFreelanceSales($this->assignmentPriorityCalculator);
        
        $customer = $this->customerRepository->ofId($payload->customerId);
        $customerJourney = $this->customerJourneyRepository->anInitialCustomerJourney();
        
        $sales = $this->assignmentPriorityCalculator->getTopPrioritySalesForCustomerAssignment($customer);
        if ($sales) {
            $payload->setId($this->assignedCustomerRepository->nextIdentity());
            $assignedCustomer = $sales->receiveCustomerAssignment($payload->id, $customer, $customerJourney);
            $this->assignedCustomerRepository->add($assignedCustomer);
            
            $this->dispatcher->dispatchEventContainer($assignedCustomer);
        }
    }
}
