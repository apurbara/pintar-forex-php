<?php

namespace Sales\Application\Service\Sales;

use Sales\Domain\Task\BySales\SalesTask;

class ExecuteSalesTask
{

    public function __construct(protected SalesRepository $salesRepository)
    {
        
    }

    public function execute(string $salesId, SalesTask $task, $payload): void
    {
        $this->salesRepository->ofId($salesId)
                ->executeTask($task, $payload);
        $this->salesRepository->update();
    }
}
