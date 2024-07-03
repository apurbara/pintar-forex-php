<?php

namespace Manager\Domain\Task\Sales;

use Manager\Domain\Model\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewAllListPayload;

class ViewAllSales implements ManagerTask
{
    public function __construct(protected SalesRepository $repository)
    {
    }
    
    /**
     * 
     * @param Manager $manager
     * @param ViewAllListPayload $payload
     * @return void
     */
    public function executeByManager(Manager $manager, $payload): void
    {
        $result = $this->repository->allSalesBelongsToManager($manager->getId(), $payload->listSchema);
        $payload->setResult($result);
    }
}
