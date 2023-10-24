<?php

namespace Company\Domain\Model\Personnel\Manager;

use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager;
use DateTimeImmutable;
use SharedContext\Domain\Enum\SalesType;
use Tests\TestBase;

class SalesTest extends TestBase
{

    protected $manager;
    protected $personnel;
    protected $area;
    //
    protected $id = 'newId', $salesType = 'IN_HOUSE';

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = $this->buildMockOfClass(Manager::class);
        $this->personnel = $this->buildMockOfClass(Personnel::class);
        $this->area = $this->buildMockOfClass(Area::class);
    }

    //
    protected function createSaleData()
    {
        return (new SalesData($this->salesType))
                        ->setId($this->id);
    }
    
    //
    protected function construct()
    {
        return new TestableSales($this->manager, $this->personnel, $this->area, $this->createSaleData());
    }
    public function test_construct_setProperties()
    {
        $sales = $this->construct();
        $this->assertSame($this->manager, $sales->manager);
        $this->assertSame($this->personnel, $sales->personnel);
        $this->assertSame($this->area, $sales->area);
        $this->assertSame($this->id, $sales->id);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($sales->createdTime);
        $this->assertFalse($sales->disabled);
        $this->assertEquals(SalesType::from($this->salesType), $sales->type);
    }
}

class TestableSales extends Sales
{

    public Manager $manager;
    public Personnel $personnel;
    public Area $area;
    public string $id;
    public DateTimeImmutable $createdTime;
    public bool $disabled;
    public SalesType $type;
}
