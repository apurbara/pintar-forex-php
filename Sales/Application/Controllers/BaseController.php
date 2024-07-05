<?php

namespace Sales\Application\Controllers;

use App\Http\Controllers\Controller;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\SalesTask;

class BaseController extends Controller
{

    protected function executeSalesMutationTask(Sales $sales, SalesTask $task, $payload): void
    {
        $sales->executeTask($task, $payload);
        $this->em->flush();
    }
}
