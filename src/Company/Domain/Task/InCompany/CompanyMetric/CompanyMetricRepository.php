<?php

namespace Company\Domain\Task\InCompany\CompanyMetric;

use Company\Domain\Model\CompanyMetric;

interface CompanyMetricRepository
{

    public function nextIdentity(): string;

    public function add(CompanyMetric $companyMetric): void;

    public function ofId(string $id): CompanyMetric;

    /**
     * 
     * @return CompanyMetric[]
     */
    public function allActive(): array;

    //
    public function aCompanyMetric(string $id): array;

    public function companyMetricList(array $paginationSchema): array;
}
