<?php

namespace Tests\Http\GraphQL\SalesBC;

use Company\Domain\Model\AreaStructure\Area;
use Sales\Domain\Model\Personnel\Sales;
use Tests\Http\GraphQL\GraphqlTestCase;
use Tests\Http\Record\EntityRecord;
use Tests\Http\Record\Model\PersonnelRecord;

class SalesBCTestCase extends GraphqlTestCase
{
    protected EntityRecord $area;
    protected PersonnelRecord $personnel;
    protected EntityRecord $sales;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Personnel')->truncate();
        $this->connection->table('Area')->truncate();
        $this->connection->table('Sales')->truncate();
        
        $this->personnel = new PersonnelRecord('main');
        $this->area = new EntityRecord(Area::class, 'main');
        
        $this->sales = new EntityRecord(Sales::class, 'main');
        $this->sales->columns['Personnel_id'] = $this->personnel->columns['id'];
        $this->sales->columns['Area_id'] = $this->area->columns['id'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Personnel')->truncate();
        $this->connection->table('Area')->truncate();
        $this->connection->table('Sales')->truncate();
    }

    protected function graphqlUri(): string
    {
        return 'graphql/sales';
    }
    
    //
    protected function prepareSalesDependency()
    {
        $this->personnel->insert($this->connection);
        $this->area->insert($this->connection);
        $this->sales->insert($this->connection);
    }
}
