<?php

namespace Tests\Http\GraphQL\UserBC\ByPersonnel;

use Tests\Http\GraphQL\GraphqlTestCase;
use Tests\Http\Record\Model\PersonnelRecord;

class PersonnelTestCase extends GraphqlTestCase
{

    protected PersonnelRecord $personnel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Personnel')->truncate();

        $this->personnel = new PersonnelRecord('main');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Personnel')->truncate();
    }

    protected function graphqlUri(): string
    {
        return 'graphql/user';
    }

    //
    protected function preparePersonnelDependency()
    {
        $this->personnel->insert($this->connection);
    }
}
