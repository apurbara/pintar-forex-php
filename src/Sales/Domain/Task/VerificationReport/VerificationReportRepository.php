<?php

namespace Sales\Domain\Task\VerificationReport;

interface VerificationReportRepository
{

    public function verificationReportListOfCustomerAssgnedToSales(string $salesId, array $paginationSchema): array;

    public function aVerificationReportOfCustomerAssgnedToSales(string $salesId, string $id): array;
}
