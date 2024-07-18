<?php

namespace Company\Domain\Task\Sales;

use Company\Domain\Model\Manager\SalesData;
use Shared\Domain\Enum\SalesType;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class UpdateSalesTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesDependency();
        $this->prepareManagerDependency();
        $this->prepareCityDependency();
        //
        $this->task = new UpdateSales($this->salesRepository, $this->managerRepository, $this->cityRepository);
        $this->payload = (new SalesData())
                ->setManagerId($this->managerId)
                ->setCityId($this->cityId)
                ->setType(SalesType::FREELANCE->value)
                ->setId($this->salesId);
    }
    
    //
    protected function execute()
    {
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_updateSales()
    {
        $this->sales->expects($this->once())
                ->method('update')
                ->with($this->manager, $this->city, $this->payload);
        $this->execute();
    }
    public function test_execute_noCityIdProvided()
    {
        $this->payload = (new SalesData())
                ->setId($this->salesId)
                ->setManagerId($this->managerId)
                ->setAccountInfoData($this->createAccountInfoData())
                ->setType(SalesType::FREELANCE->value);
        $this->execute();
        $this->markAsSuccess();
    }
}
