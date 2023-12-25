<?php

namespace Manager\Domain\Task\AssignedCustomer;

use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewAssignedCustomerList implements ManagerTask
{

    public function __construct(protected AssignedCustomerRepository $assignedCustomerRepository)
    {
        
    }

    /**
     * 
     * @param Manager $manager
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $result = $this->assignedCustomerRepository
                ->assignedCustomerListManagedByManager($manager->getId(), $payload->paginationSchema);
        $payload->setResult($result);
    }
}
