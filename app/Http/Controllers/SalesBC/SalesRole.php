<?php

namespace App\Http\Controllers\SalesBC;

use Doctrine\ORM\EntityManager;
use Sales\Application\Service\Sales\ExecuteSalesTask;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\BySales\SalesTask;
use function app;

class SalesRole
{

    protected EntityManager $em;

    public function __construct(public readonly string $salesId)
    {
        $this->em = app(EntityManager::class);
    }

    public function executeSalesTask(SalesTask $task, $payload): void
    {
        $salesRepository = $this->em->getRepository(Sales::class);
        (new ExecuteSalesTask($salesRepository))
                ->execute($this->salesId, $task, $payload);
    }
}
