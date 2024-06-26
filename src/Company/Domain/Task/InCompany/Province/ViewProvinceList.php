<?php

namespace Company\Domain\Task\InCompany\Province;

use Company\Domain\Model\AdminTaskInCompany;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;

class ViewProvinceList implements AdminTaskInCompany
{
    public function __construct(protected ProvinceRepository $repository)
    {
    }
    
    /**
     * 
     * @param ViewPaginationListPayload $payload
     * @return void
     */
    public function executeInCompany($payload): void
    {
        $result = $this->repository->provinceList($payload->paginationSchema);
        $payload->setResult($result);
    }
}
