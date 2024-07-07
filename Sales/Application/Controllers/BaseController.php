<?php

namespace Sales\Application\Controllers;

use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesTask;
use Shared\Application\Controllers\Controller;

class BaseController extends Controller
{

    protected function executeSalesMutationTask(Sales $sales, SalesTask $task, $payload): void
    {
        $sales->executeTask($task, $payload);
        $this->em->flush();
    }
}
