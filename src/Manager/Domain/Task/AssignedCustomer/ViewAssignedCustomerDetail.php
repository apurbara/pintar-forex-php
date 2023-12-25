<?php

namespace Manager\Domain\Task\AssignedCustomer;

use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewAssignedCustomerDetail implements ManagerTask
{

    public function __construct(protected AssignedCustomerRepository $assignedCustomerRepository)
    {
        
    }

    /**
     * 
     * @param Manager $manager
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $result = $this->assignedCustomerRepository->anAssignedCustomerManagedByManager($manager->getId(), $payload->id);
        $payload->setResult($result);
    }
}
