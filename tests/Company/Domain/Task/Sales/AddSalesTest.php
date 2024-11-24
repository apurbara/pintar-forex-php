<?php

namespace Company\Domain\Task\Sales;

use Company\Domain\Model\Manager\SalesData;
use Shared\Domain\Enum\SalesRole;
use Shared\Domain\Enum\SalesType;
use Tests\Company\Domain\Task\TaskInCompanyTestBase;

class AddSalesTest extends TaskInCompanyTestBase
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
        $this->task = new AddSales($this->salesRepository, $this->managerRepository, $this->cityRepository);
        $this->payload = (new SalesData())
                ->setManagerId($this->managerId)
                ->setCityId($this->cityId)
                ->setAccountInfoData($this->createAccountInfoData())
                ->setRole(SalesRole::FACT_FINDER->value);
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
                ->setManagerId($this->managerId)
                ->setAccountInfoData($this->createAccountInfoData())
                ->setRole(SalesRole::FACT_FINDER->value);
        $this->execute();
    }
}
