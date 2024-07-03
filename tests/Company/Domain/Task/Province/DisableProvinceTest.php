<?php

namespace Company\Domain\Task\Province;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class DisableProvinceTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareProvinceDependency();
        //
        $this->task = new DisableProvince($this->provinceRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->provinceId);
    }
    public function test_execute_disableProvince()
    {
        $this->province->expects($this->once())
                ->method('disable');
        $this->execute();
    }
}
