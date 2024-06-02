<?php

namespace User\Application\Service\Guest;

use Tests\TestBase;
use User\Domain\Model\Manager;

class ManagerLoginServiceTest extends TestBase
{
    protected $managerRepository;
    protected $manager, $managerId = 'managerId';
    protected $service;
    //
    protected $email = 'manager@email.org', $password = 'password123';
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->managerRepository = $this->buildMockOfInterface(ManagerRepository::class);
        $this->service = new ManagerLoginService($this->managerRepository);
        
        $this->manager = $this->buildMockOfClass(Manager::class);
    }
    
    //
    protected function execute()
    {
        $this->managerRepository->expects($this->any())
                ->method('ofEmail')
                ->with($this->email)
                ->willReturn($this->manager);
        return $this->service->execute($this->email, $this->password);
    }
    public function test_execute_returnManagerLoginResult()
    {
        $this->manager->expects($this->once())
                ->method('login')
                ->with($this->password)
                ->willReturn($this->managerId);
        $this->assertSame($this->managerId, $this->execute());
    }
}
