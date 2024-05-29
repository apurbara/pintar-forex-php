<?php

namespace Sales\Domain\Task\BySales\VerificationReport;

use Resources\Domain\TaskPayload\ViewDetailPayload;
use Sales\Domain\Model\Sales;
use Sales\Domain\Task\BySales\SalesTask;

class ViewVerificationReportDetailTask implements SalesTask
{

    public function __construct(protected VerificationReportRepository $verificationReportRepository)
    {
        
    }

    /**
     * 
     * @param Sales $sales
     * @param ViewDetailPayload $payload
     * @return void
     */
    public function executeBySales(Sales $sales, $payload): void
    {
        $result = $this->verificationReportRepository
                ->aVerificationReportOfCustomerAssgnedToSales($sales->getId(), $payload->id);
        $payload->setResult($result);
    }
}
