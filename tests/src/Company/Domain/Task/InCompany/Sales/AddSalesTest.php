<?php

namespace Company\Domain\Task\InCompany\Sales;

use Company\Domain\Model\SalesData;
use SharedContext\Domain\Enum\SalesType;
use Tests\src\Company\Domain\Task\InCompany\TaskInCompanyTestBase;

class AddSalesTest extends TaskInCompanyTestBase
{
    protected $task;
    protected $payload;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareSalesDependency();
        $this->prepareAreaDependency();
        //
        $this->task = new AddSales($this->salesRepository, $this->areaRepository);
        $this->payload = (new SalesData())
                ->setAccountInfoData($this->createAccountInfoData())
                ->setType(SalesType::FREELANCE->value)
                ->setAreaId($this->areaId);
    }
    
    //
    protected function execute()
    {
        $this->salesRepository->expects($this->once())
                ->method('nextIdentity')
                ->willReturn($this->salesId);
        $this->salesRepository->expects($this->any())
                ->method('isEmailAvailable')
                ->willReturn(true);
        $this->task->executeInCompany($this->payload);
    }
    public function test_execute_setPayloadId()
    {
        $this->execute();
        $this->assertSame($this->salesId, $this->payload->id);
    }
    public function test_execute_emailUnavailable()
    {
        $this->salesRepository->expects($this->once())
                ->method('isEmailAvailable')
                ->with($this->payload->accountInfoData->email)
                ->willReturn(false);
        $this->assertRegularExceptionThrowed(fn() => $this->execute(), 'Conflict', 'email already registered');
    }
    public function test_execute_addSalesToRepository()
    {
        $this->salesRepository->expects($this->once())
                ->method('add');
        $this->execute();
    }
    public function test_execute_noAreaIdProvided()
    {
        $this->payload = (new SalesData())
                ->setAccountInfoData($this->createAccountInfoData())
                ->setType(SalesType::FREELANCE->value);
        $this->execute();
    }
}
