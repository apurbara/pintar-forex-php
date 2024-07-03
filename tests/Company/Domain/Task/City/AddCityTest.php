<?php

namespace Company\Domain\Task\City;

use Company\Domain\Model\Province\CityData;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class AddCityTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareProvinceDependency();
        $this->prepareCityDependency();
        
        $this->task = new AddCity($this->cityRepository, $this->provinceRepository);
        $this->payload = (new CityData())
                ->setName('new city name')
                ->setProvinceId($this->provinceId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_setPayloadId()
    {
        $this->cityRepository->expects($this->once())
                ->method('nextIdentity')
                ->willReturn($this->cityId);
        $this->execute();
        $this->assertSame($this->cityId, $this->payload->id);
    }
    public function test_execute_addCityToRepository()
    {
        $this->cityRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
}
