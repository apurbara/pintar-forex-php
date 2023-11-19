<?php

namespace Tests\Http\GraphQL\ManagerBC;

use Manager\Domain\Model\Personnel\Manager;
use Tests\Http\GraphQL\GraphqlTestCase;
use Tests\Http\Record\EntityRecord;
use Tests\Http\Record\Model\PersonnelRecord;

class ManagerBCTestCase extends GraphqlTestCase
{
    protected PersonnelRecord $personnel;
    protected EntityRecord $manager;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Personnel')->truncate();
        $this->connection->table('Manager')->truncate();
        
        $this->personnel = new PersonnelRecord('main');
        
        $this->manager = new EntityRecord(Manager::class, 'main');
        $this->manager->columns['Personnel_id'] = $this->personnel->columns['id'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Personnel')->truncate();
        $this->connection->table('Manager')->truncate();
    }

    protected function graphqlUri(): string
    {
        return 'graphql/manager';
    }
    
    //
    protected function prepareManagerDependency()
    {
        $this->personnel->insert($this->connection);
        $this->manager->insert($this->connection);
    }
}
