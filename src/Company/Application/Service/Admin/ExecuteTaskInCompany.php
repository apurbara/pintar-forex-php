<?php

namespace Company\Application\Service\Admin;

use Company\Domain\Model\AdminTaskInCompany;

class ExecuteTaskInCompany
{
    public function __construct(protected AdminRepository $adminRepository)
    {
    }
    
    public function execute(string $id, AdminTaskInCompany $task, $payload): void
    {
        $this->adminRepository->ofId($id)
                ->executeTaskInCompany($task, $payload);
        $this->adminRepository->update();
    }
}
