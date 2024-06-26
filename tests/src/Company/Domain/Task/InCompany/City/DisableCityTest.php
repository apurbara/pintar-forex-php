<?php

namespace Company\Domain\Task\InCompany\City;

use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class DisableCityTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCityDependency();
        
        $this->task = new DisableCity($this->cityRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->cityId);
    }
    public function test_execute_updateCity()
    {
        $this->city->expects($this->once())
                ->method('disable');
        $this->execute();
    }
}
