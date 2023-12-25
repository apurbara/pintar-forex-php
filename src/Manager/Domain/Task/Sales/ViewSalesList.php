<?php

namespace Manager\Domain\Task\Sales;

use Manager\Domain\Model\Personnel\Manager;
use Manager\Domain\Task\ManagerTask;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewSalesList implements ManagerTask
{

    public function __construct(protected SalesRepository $salesRepository)
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
        $result = $this->salesRepository->salesListManagedByManager($manager->getId(), $payload->paginationSchema);
        $payload->setResult($result);
    }
}
