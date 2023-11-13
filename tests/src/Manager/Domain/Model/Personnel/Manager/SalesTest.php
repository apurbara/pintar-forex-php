<?php

namespace Manager\Domain\Model\Personnel\Manager;

use Manager\Domain\Model\Personnel\Manager;
use Tests\TestBase;

class SalesTest extends TestBase
{
    protected $sales;
    protected $manager;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->sales = new TestableSales();
        $this->manager = $this->buildMockOfClass(Manager::class);
        $this->sales->manager = $this->manager;
    }
    
    //
    protected function isManageableByManager()
    {
        return $this->sales->isManageableByManager($this->manager);
    }
    public function test_isManageableByManager_sameManager_returnTrue()
    {
        $this->assertTrue($this->isManageableByManager());
    }
    public function test_isManageableByManager_diffManager_returnFalse()
    {
        $this->sales->manager = $this->buildMockOfClass(Manager::class);
        $this->assertFalse($this->isManageableByManager());
    }
}

class TestableSales extends Sales
{

    public Manager $manager;
    public string $id;
    public bool $disabled;
    
    function __construct()
    {
        parent::__construct();
    }
}
