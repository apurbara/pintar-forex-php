<?php

namespace Tests\Http\GraphQL\UserBC\BySales;

use Tests\Http\GraphQL\GraphqlTestCase;
use Tests\Http\Record\Model\SalesRecord;


class SalesTestCase extends GraphqlTestCase
{

    protected SalesRecord $sales;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Sales')->truncate();

        $this->sales = new SalesRecord('main');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Sales')->truncate();
    }

    protected function graphqlUri(): string
    {
        return 'graphql/user';
    }

    //
    protected function prepareSalesDependency()
    {
        $this->sales->insert($this->connection);
    }
}
