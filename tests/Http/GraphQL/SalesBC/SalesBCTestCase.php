<?php

namespace Tests\Http\GraphQL\SalesBC;

use Company\Domain\Model\AreaStructure\Area;
use Tests\Http\GraphQL\GraphqlTestCase;
use Tests\Http\Record\EntityRecord;
use Tests\Http\Record\Model\SalesRecord;

class SalesBCTestCase extends GraphqlTestCase
{
    protected EntityRecord $area;
    protected SalesRecord $sales;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Area')->truncate();
        $this->connection->table('Sales')->truncate();
        
        $this->area = new EntityRecord(Area::class, 'main');
        
        $this->sales = new SalesRecord('main');
        $this->sales->columns['Area_id'] = $this->area->columns['id'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
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
        $this->area->insert($this->connection);
        $this->sales->insert($this->connection);
    }
}
