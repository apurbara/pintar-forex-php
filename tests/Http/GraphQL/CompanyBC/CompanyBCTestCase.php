<?php

namespace Tests\Http\GraphQL\CompanyBC;

use Tests\Http\GraphQL\GraphqlTestCase;
use Tests\Http\Record\Model\AdminRecord;
use Tests\Http\Record\Model\ManagerRecord;
use Tests\Http\Record\Model\SalesRecord;

class CompanyBCTestCase extends GraphqlTestCase
{
    protected AdminRecord $admin;
    protected ManagerRecord $manager;
    protected SalesRecord $sales;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Admin')->truncate();
        $this->connection->table('Manager')->truncate();
        $this->connection->table('Sales')->truncate();
        
        $this->admin = new AdminRecord('main');
        $this->manager = new ManagerRecord('main');
        $this->sales = new SalesRecord('main');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Admin')->truncate();
        $this->connection->table('Manager')->truncate();
        $this->connection->table('Sales')->truncate();
    }

    protected function graphqlUri(): string
    {
        return 'graphql/company';
    }
    
    //
    protected function prepareAdminDependency()
    {
        $this->admin->insert($this->connection);
    }
    
    //
    protected function prepareManagerDependency()
    {
        $this->manager->insert($this->connection);
    }
    
    //
    protected function prepareSalesDependency()
    {
        $this->sales->insert($this->connection);
    }
}
