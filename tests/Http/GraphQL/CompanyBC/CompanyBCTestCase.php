<?php

namespace Tests\Http\GraphQL\CompanyBC;

use Tests\Http\GraphQL\GraphqlTestCase;
use Tests\Http\Record\Model\AdminRecord;
use Tests\Http\Record\Model\ManagerRecord;

class CompanyBCTestCase extends GraphqlTestCase
{
    protected AdminRecord $admin;
    protected ManagerRecord $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Admin')->truncate();
        $this->connection->table('Manager')->truncate();
        
        $this->admin = new AdminRecord('main');
        //
        $this->manager = new ManagerRecord('main');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Admin')->truncate();
        $this->connection->table('Manager')->truncate();
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
}
