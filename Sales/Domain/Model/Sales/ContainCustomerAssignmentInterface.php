<?php

namespace Sales\Domain\Model\Sales;

use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReportData;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;

interface ContainCustomerAssignmentInterface
{

    public function submitNonScheduledSalesActivityReport(
            SalesActivity $salesActivity, string $reportId, SalesActivityReportData $salesActivityReportData): SalesActivityReport;

    public function submitSalesActivitySchedule(
            SalesActivity $salesActivity, string $scheduleId, SalesActivityScheduleData $salesActivityScheduleData): SalesActivitySchedule;

    public function assertBelongsToSales(Sales $sales): void;
}
