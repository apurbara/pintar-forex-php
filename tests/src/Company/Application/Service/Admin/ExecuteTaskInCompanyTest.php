<?php

namespace Company\Application\Service\Admin;

use Company\Domain\Model\Admin;
use Company\Domain\Model\AdminTaskInCompany;
use Tests\TestBase;

class ExecuteTaskInCompanyTest extends TestBase
{
    protected $adminRepository;
    protected $admin;
    protected $adminId = 'adminId';
    //
    protected $service;
    protected $task, $payload = 'string represent task payload';

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminRepository = $this->buildMockOfInterface(AdminRepository::class);
        $this->admin = $this->buildMockOfClass(Admin::class);
        $this->adminRepository->expects($this->any())
                ->method('ofId')
                ->with($this->adminId)
                ->willReturn($this->admin);
        //
        $this->service = new ExecuteTaskInCompany($this->adminRepository);
        $this->task = $this->buildMockOfInterface(AdminTaskInCompany::class);
    }
    
    //
    protected function execute()
    {
        $this->service->execute($this->adminId, $this->task, $this->payload);
    }
    public function test_execute_adminExecuteTask()
    {
        $this->admin->expects($this->once())
                ->method('executeTaskInCompany')
                ->with($this->task, $this->payload);
        $this->execute();
    }
    public function test_execute_updateRepository()
    {
        $this->adminRepository->expects($this->once())
                ->method('update');
        $this->execute();
    }
}
