<?php

namespace Tests\Sales\Application\Controllers;

use Company\Domain\Model\Province\City;
use Manager\Domain\Model\Manager;
use Tests\resources\Application\EntityRecord;
use Tests\Shared\Application\GraphqlTestCase;

class SalesControllerTestCase extends GraphqlTestCase
{
    protected EntityRecord $city;
    protected EntityRecord $manager;
    protected SalesRecord $sales;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('City')->truncate();
        $this->connection->table('Manager')->truncate();
        $this->connection->table('Sales')->truncate();
        
        $this->city = new EntityRecord(City::class, 'main');
        $this->manager = new EntityRecord(Manager::class, 'main');
        
        $this->sales = new SalesRecord('main');
        $this->sales->columns['Manager_id'] = $this->manager->columns['id'];
        $this->sales->columns['City_id'] = $this->city->columns['id'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('City')->truncate();
        $this->connection->table('Manager')->truncate();
        $this->connection->table('Sales')->truncate();
    }

    protected function graphqlUri(): string
    {
        return 'graphql/sales';
    }
    
    //
    protected function prepareSalesDependency()
    {
        $this->city->insert($this->connection);
        $this->manager->insert($this->connection);
        $this->sales->insert($this->connection);
    }
}
