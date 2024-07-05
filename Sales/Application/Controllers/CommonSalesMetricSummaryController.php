<?php

namespace Sales\Application\Controllers;

use Resources\Domain\TaskPayload\ViewPayload;
use Sales\Domain\DependencyModel\CommonSalesMetric;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\CommonSalesMetricSummary\ViewAllCommonSalesMetricSummary;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineCommonSalesMetricSummaryRepository;

class CommonSalesMetricSummaryController extends BaseController
{

    public function viewAllCommonSalesMetricSummary(Sales $sales)
    {
        $repository = new DoctrineCommonSalesMetricSummaryRepository($this->em);
        $commonSalesRepository = $this->em->getRepository(CommonSalesMetric::class);
        $task = new ViewAllCommonSalesMetricSummary($repository, $commonSalesRepository);
        $payload = new ViewPayload();

        $sales->executeTask($task, $payload);
        return $payload->result;
    }
}
