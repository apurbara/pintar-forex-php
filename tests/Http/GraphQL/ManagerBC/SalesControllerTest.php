<?php

namespace Tests\Http\GraphQL\ManagerBC;

use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager\Sales;
use Tests\Http\Record\EntityRecord;

class SalesControllerTest extends ManagerBCTestCase
{

    protected EntityRecord $personnelOne;
    protected EntityRecord $personnelTwo;
    protected EntityRecord $salesOne;
    protected EntityRecord $salesTwo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Sales')->truncate();

        $this->personnelOne = new EntityRecord(Personnel::class, 1);
        $this->personnelTwo = new EntityRecord(Personnel::class, 2);

        $this->salesOne = new EntityRecord(Sales::class, 1);
        $this->salesOne->columns['Personnel_id'] = $this->personnelOne->columns['id'];
        $this->salesOne->columns['Manager_id'] = $this->manager->columns['id'];
        $this->salesTwo = new EntityRecord(Sales::class, 2);
        $this->salesTwo->columns['Personnel_id'] = $this->personnelTwo->columns['id'];
        $this->salesTwo->columns['Manager_id'] = $this->manager->columns['id'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Sales')->truncate();
    }

    //
    protected function salesList()
    {
        $this->prepareManagerDependency();
        $this->personnelOne->insert($this->connection);
        $this->personnelTwo->insert($this->connection);
        $this->salesOne->insert($this->connection);
        $this->salesTwo->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query ( $managerId: ID! ) {
    manager ( managerId: $managerId ) {
        salesList {
            list { id, disabled, personnel { name } }
            cursorLimit { total }
        }
    }
}
_QUERY;
        $this->graphqlVariables = ['managerId' => $this->manager->columns['id']];
        $this->postGraphqlRequest($this->personnel->token);
    }

    public function test_salesList_200()
    {
        $this->salesList();
        $this->seeStatusCode(200);

        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->salesOne->columns['id'],
                    'disabled' => $this->salesOne->columns['disabled'],
                    'personnel' => [
                        'name' => $this->personnelOne->columns['name'],
                    ],
                ],
                [
                    'id' => $this->salesTwo->columns['id'],
                    'disabled' => $this->salesTwo->columns['disabled'],
                    'personnel' => [
                        'name' => $this->personnelTwo->columns['name'],
                    ],
                ],
            ],
            'cursorLimit' => ['total' => 2],
        ]);
    }

    //
    protected function salesDetail()
    {
        $this->prepareManagerDependency();
        $this->personnelOne->insert($this->connection);
        $this->salesOne->insert($this->connection);

        $this->graphqlQuery = <<<'_QUERY'
query ( $managerId: ID!, $id: ID ) {
    manager ( managerId: $managerId ) {
        salesDetail (id: $id) {
            id, disabled, personnel { name }
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'managerId' => $this->manager->columns['id'],
            'id' => $this->salesOne->columns['id']
        ];
        $this->postGraphqlRequest($this->personnel->token);
    }

    public function test_salesDetail_200()
    {
        $this->salesDetail();
        $this->seeStatusCode(200);

        $this->seeJsonContains([
            'id' => $this->salesOne->columns['id'],
            'disabled' => $this->salesOne->columns['disabled'],
            'personnel' => [
                'name' => $this->personnelOne->columns['name'],
            ],
        ]);
    }
}
