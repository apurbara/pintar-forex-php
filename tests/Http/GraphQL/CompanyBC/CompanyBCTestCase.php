<?php

namespace Tests\Http\GraphQL\CompanyBC;

use Company\Domain\Model\Personnel\Manager;
use Tests\Http\GraphQL\GraphqlTestCase;
use Tests\Http\Record\EntityRecord;
use Tests\Http\Record\Model\AdminRecord;
use Tests\Http\Record\Model\PersonnelRecord;

class CompanyBCTestCase extends GraphqlTestCase
{
    protected AdminRecord $admin;
    protected PersonnelRecord $personnel;
    //
    protected EntityRecord $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Admin')->truncate();
        $this->connection->table('Personnel')->truncate();
        $this->connection->table('Manager')->truncate();
        
        $this->admin = new AdminRecord('main');
        $this->personnel = new PersonnelRecord('main');
        //
        $this->manager = new EntityRecord(Manager::class, 'main');
        $this->manager->columns['Personnel_id'] = $this->personnel->columns['id'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Admin')->truncate();
        $this->connection->table('Personnel')->truncate();
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
    protected function preparePersonnelDependency()
    {
        $this->personnel->insert($this->connection);
        $this->manager->insert($this->connection);
    }
}
