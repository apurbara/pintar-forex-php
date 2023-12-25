<?php

namespace Manager\Domain\Task\Sales;

use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class ViewSalesDetail implements ManagerTask
{

    public function __construct(protected SalesRepository $salesRepository)
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
        $result = $this->salesRepository->aSalesManagedByManager($manager->getId(), $payload->id);
        $payload->setResult($result);
    }
}
