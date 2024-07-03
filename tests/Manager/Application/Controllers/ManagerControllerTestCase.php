<?php

namespace Tests\Manager\Application\Controllers;

use Tests\Http\GraphQL\GraphqlTestCase;


class ManagerControllerTestCase extends GraphqlTestCase
{
    protected ManagerRecord $manager;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Manager')->truncate();
        
        $this->manager = new ManagerRecord('main');
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Manager')->truncate();
    }

    protected function graphqlUri(): string
    {
        return 'graphql/manager';
    }
    
    protected function persistManagerDependency(): void
    {
        $this->manager->insert($this->connection);
    }
}
