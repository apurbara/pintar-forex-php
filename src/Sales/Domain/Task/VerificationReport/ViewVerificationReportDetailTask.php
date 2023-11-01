<?php

namespace Sales\Domain\Task\VerificationReport;

use Resources\Domain\TaskPayload\ViewDetailPayload;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Task\SalesTask;

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
