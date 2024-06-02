<?php

namespace App\Http\Controllers\SalesBC\BySales;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SalesBC\SalesRole;
use Resources\Domain\TaskPayload\ViewPayload;
use Sales\Domain\DependencyModel\CommonSalesMetric;
use Sales\Domain\Task\BySales\CommonSalesMetricSummary\ViewAllCommonSalesMetricSummary;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineCommonSalesMetricSummaryRepository;

class CommonSalesMetricSummaryController extends Controller
{

    public function viewAllCommonSalesMetricSummary(SalesRole $user)
    {
        $repository = new DoctrineCommonSalesMetricSummaryRepository($this->em);
        $commonSalesRepository = $this->em->getRepository(CommonSalesMetric::class);
        $task = new ViewAllCommonSalesMetricSummary($repository, $commonSalesRepository);
        $payload = new ViewPayload();

        $user->executeSalesTask($task, $payload);
        return $payload->result;
    }
}
