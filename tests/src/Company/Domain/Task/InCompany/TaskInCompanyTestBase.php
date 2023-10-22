<?php

namespace Tests\src\Company\Domain\Task\InCompany;

use Company\Domain\Model\Personnel;
use Company\Domain\Task\InCompany\Personnel\PersonnelRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestBase;

class TaskInCompanyTestBase extends TestBase
{
    protected MockObject $personnelRepository;
    protected MockObject $personnel;
    protected string $personnelId = 'personnelId';
    
    protected function preparePersonnelDependency(): void
    {
        $this->personnelRepository = $this->buildMockOfInterface(PersonnelRepository::class);
        $this->personnel = $this->buildMockOfClass(Personnel::class);
    }
}
