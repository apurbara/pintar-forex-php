<?php

namespace Company\Domain\Task\City;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class EnableCityTest extends TaskInCompanyTestBase
{
    protected $task;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCityDependency();
        
        $this->task = new EnableCity($this->cityRepository);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->cityId);
    }
    public function test_execute_updateCity()
    {
        $this->city->expects($this->once())
                ->method('enable');
        $this->execute();
    }
}
