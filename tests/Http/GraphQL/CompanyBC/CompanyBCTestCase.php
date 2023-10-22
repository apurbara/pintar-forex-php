<?php

namespace Tests\Http\GraphQL\CompanyBC;

use Tests\Http\GraphQL\GraphqlTestCase;
use Tests\Http\Record\Model\AdminRecord;

class CompanyBCTestCase extends GraphqlTestCase
{
    protected AdminRecord $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Admin')->truncate();
        
        $this->admin = new AdminRecord('main');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Admin')->truncate();
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
}
