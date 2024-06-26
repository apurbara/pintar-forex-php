<?php

namespace Company\Domain\Task\InCompany\Province;

use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class EnableProvinceTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareProvinceDependency();
        //
        $this->task = new EnableProvince($this->provinceRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->provinceId);
    }
    public function test_execute_enableProvince()
    {
        $this->province->expects($this->once())
                ->method('enable');
        $this->execute();
    }
}
