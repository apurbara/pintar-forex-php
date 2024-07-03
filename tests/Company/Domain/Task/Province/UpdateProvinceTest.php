<?php

namespace Company\Domain\Task\Province;

use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class UpdateProvinceTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareProvinceDependency();
        //
        $this->task = new UpdateProvince($this->provinceRepository);
        $this->payload = (new \Company\Domain\Model\ProvinceData())
                ->setId($this->provinceId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_updateProvince()
    {
        $this->province->expects($this->once())
                ->method('update')
                ->with($this->payload);
        $this->execute();
    }
}
