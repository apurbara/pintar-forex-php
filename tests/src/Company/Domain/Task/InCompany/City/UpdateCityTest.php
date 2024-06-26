<?php

namespace Company\Domain\Task\InCompany\City;

use Company\Domain\Model\Province\CityData;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class UpdateCityTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareCityDependency();
        $this->prepareProvinceDependency();
        
        $this->task = new UpdateCity($this->cityRepository, $this->provinceRepository);
        $this->payload = (new CityData())
                ->setId($this->cityId)
                ->setProvinceId($this->provinceId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_updateCity()
    {
        $this->city->expects($this->once())
                ->method('update')
                ->with($this->province, $this->payload);
        $this->execute();
    }
}
