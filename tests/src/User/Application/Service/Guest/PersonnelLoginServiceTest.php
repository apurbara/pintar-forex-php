<?php

namespace User\Application\Service\Guest;

use Tests\TestBase;
use User\Domain\Model\Personnel;

class PersonnelLoginServiceTest extends TestBase
{
    protected $personnelRepository;
    protected $personnel, $personnelId = 'personnelId';
    protected $service;
    //
    protected $email = 'personnel@email.org', $password = 'password123';
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->personnelRepository = $this->buildMockOfInterface(PersonnelRepository::class);
        $this->service = new PersonnelLoginService($this->personnelRepository);
        
        $this->personnel = $this->buildMockOfClass(Personnel::class);
    }
    
    //
    protected function execute()
    {
        $this->personnelRepository->expects($this->any())
                ->method('ofEmail')
                ->with($this->email)
                ->willReturn($this->personnel);
        return $this->service->execute($this->email, $this->password);
    }
    public function test_execute_returnPersonnelLoginResult()
    {
        $this->personnel->expects($this->once())
                ->method('login')
                ->with($this->password)
                ->willReturn($this->personnelId);
        $this->assertSame($this->personnelId, $this->execute());
    }
}
