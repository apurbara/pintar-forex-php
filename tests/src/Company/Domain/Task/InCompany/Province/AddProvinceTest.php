<?php

namespace Company\Domain\Task\InCompany\Province;

use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class AddProvinceTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareProvinceDependency();
        //
        $this->task = new AddProvince($this->provinceRepository);
        $this->payload = (new \Company\Domain\Model\ProvinceData())
                ->setName('new name');
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_setProvinceDataId()
    {
        $this->provinceRepository->expects($this->once())
                ->method('nextIdentity')
                ->willReturn($this->provinceId);
        $this->execute();
        $this->assertSame($this->provinceId, $this->payload->id);
    }
    public function test_execute_addProvinceToRepository()
    {
        $this->provinceRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
}
