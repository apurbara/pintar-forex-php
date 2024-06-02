<?php

namespace Tests\src\User\Domain\Task\ByManager;

use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestBase;
use User\Domain\Model\Manager;

class ManagerTaskTestBase extends TestBase
{

    protected MockObject $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = $this->buildMockOfClass(Manager::class);
    }
}
