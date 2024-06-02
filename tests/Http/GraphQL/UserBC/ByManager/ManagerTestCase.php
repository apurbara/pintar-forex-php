<?php

namespace Tests\Http\GraphQL\UserBC\ByManager;

use Tests\Http\GraphQL\GraphqlTestCase;
use Tests\Http\Record\Model\ManagerRecord;


class ManagerTestCase extends GraphqlTestCase
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
        return 'graphql/user';
    }

    //
    protected function prepareManagerDependency()
    {
        $this->manager->insert($this->connection);
    }
}
