<?php

namespace User\Application\Service\Guest;

use Tests\TestBase;
use User\Domain\Model\Admin;

class AdminLoginServiceTest extends TestBase
{
    protected $adminRepository;
    protected $admin, $adminId = 'adminId';
    protected $service;
    //
    protected $email = 'admin@email.org', $password = 'password123';
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->adminRepository = $this->buildMockOfInterface(AdminRepository::class);
        $this->service = new AdminLoginService($this->adminRepository);
        
        $this->admin = $this->buildMockOfClass(Admin::class);
    }
    
    //
    protected function execute()
    {
        $this->adminRepository->expects($this->any())
                ->method('ofEmail')
                ->with($this->email)
                ->willReturn($this->admin);
        return $this->service->execute($this->email, $this->password);
    }
    public function test_execute_returnAdminLoginResult()
    {
        $this->admin->expects($this->once())
                ->method('login')
                ->with($this->password)
                ->willReturn($this->adminId);
        $this->assertSame($this->adminId, $this->execute());
    }
}
