<?php

namespace Tests\src\User\Domain\Task\BySales;

use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestBase;
use User\Domain\Model\Sales;

class SalesTaskTestBase extends TestBase
{

    protected MockObject $sales;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = $this->buildMockOfClass(Sales::class);
    }
}
