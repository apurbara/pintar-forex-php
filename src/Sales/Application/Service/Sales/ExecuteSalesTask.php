<?php

namespace Sales\Application\Service\Sales;

use Sales\Domain\Task\SalesTask;

class ExecuteSalesTask
{

    public function __construct(protected SalesRepository $salesRepository)
    {
        
    }

    public function excute(string $personnelId, string $salesId, SalesTask $task, $payload): void
    {
        $this->salesRepository->aSalesBelongToPersonnel($personnelId, $salesId)
                ->executeTask($task, $payload);
        $this->salesRepository->update();
    }
}
