<?php

namespace Tests\Admin\Application\Controllers;

use Tests\Shared\Application\AdminRecord;
use Tests\Shared\Application\GraphqlTestCase;

class AdminControllerTestCase extends GraphqlTestCase
{

    protected AdminRecord $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Admin')->truncate();
        //
        $this->admin = new AdminRecord('main');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Admin')->truncate();
    }

    //
    protected function persistAdminDependency()
    {
        $this->admin->insert($this->connection);
    }

    protected function graphqlUri(): string
    {
        return 'graphql/admin';
    }
}
