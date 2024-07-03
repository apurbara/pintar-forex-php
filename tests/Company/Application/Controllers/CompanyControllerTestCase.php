<?php

namespace Tests\Company\Application\Controllers;

use Tests\Shared\Application\AdminRecord;
use Tests\Shared\Application\GraphqlTestCase;
use Tests\Shared\Application\ManagerRecord;
use Tests\Shared\Application\SalesRecord;

class CompanyControllerTestCase extends GraphqlTestCase
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
