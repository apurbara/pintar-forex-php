<?php

namespace App\Http\Controllers\UserBC\BySales;

use Doctrine\ORM\EntityManager;
use User\Application\Service\Sales\ExecuteSalesTask;
use User\Domain\Model\Sales;
use User\Domain\Task\BySales\SalesTask;
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
