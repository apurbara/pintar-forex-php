<?php

namespace Company\Application\Service\Sales;

use Company\Domain\Model\SalesTaskInCompany;

class ExecuteTaskInCompany
{

    public function __construct(protected SalesRepository $repository)
    {
        
    }

    public function execute(string $salesId, SalesTaskInCompany $task, $payload): void
    {
        $this->repository->ofId($salesId)
                ->executeTaskInCompany($task, $payload);
        $this->repository->update();
    }
}
