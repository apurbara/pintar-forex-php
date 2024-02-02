<?php

namespace Tests\src\User\Domain\Task\ByPersonnel;

use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestBase;
use User\Domain\Model\Personnel;

class PersonnelTaskTestBase extends TestBase
{

    protected MockObject $personnel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->personnel = $this->buildMockOfClass(Personnel::class);
    }
}
