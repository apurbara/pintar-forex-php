<?php

namespace Manager\Application\Controllers;

use Company\Domain\Model\Customer;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Domain\Model\Manager\Sales\FactFindingAssignment;
use Company\Domain\Model\Manager\Sales\FactFindingAssignment\ClosingRequestByFactFinder;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\CustomerStatus;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Tests\Manager\Application\Controllers\ManagerControllerTestCase;
use Tests\resources\Application\EntityRecord;

class ClosingRequestByFactFinderControllerTest extends ManagerControllerTestCase
{
    protected $salesOne;
    protected $salesTwo;
    
    protected $customerOne;
    protected $customerTwo;

    protected $factFindingAssignmentOne, $customerAssignmentOne;
    protected $factFindingAssignmentTwo, $customerAssignmentTwo;

    protected $closingRequestByFactFinderOne;
    protected $closingRequestByFactFinderTwo;

    protected $closingRequestByFactFinderPayload = ['remark' => 'manager remark'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('FactFindingAssignment')->truncate();
        $this->connection->table('ClosingRequestByFactFinder')->truncate();
        
        $this->salesOne = new EntityRecord(Sales::class, 1);
        $this->salesOne->columns['Manager_id'] = $this->manager->columns['id'];
        $this->salesTwo = new EntityRecord(Sales::class, 2);
        $this->salesTwo->columns['Manager_id'] = $this->manager->columns['id'];
        
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        
        $this->customerAssignmentOne = new EntityRecord(CustomerAssignment::class, 'One');
        $this->factFindingAssignmentOne = new EntityRecord(FactFindingAssignment::class, 'One');
        $this->factFindingAssignmentOne->columns['CustomerAssignment_id'] = $this->customerAssignmentOne->columns['id'];
        $this->factFindingAssignmentOne->columns['id'] = $this->customerAssignmentOne->columns['id'];
        $this->factFindingAssignmentOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->factFindingAssignmentOne->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->customerAssignmentTwo = new EntityRecord(CustomerAssignment::class, 'Two');
        $this->factFindingAssignmentTwo = new EntityRecord(FactFindingAssignment::class, 'Two');
        $this->factFindingAssignmentTwo->columns['CustomerAssignment_id'] = $this->customerAssignmentTwo->columns['id'];
        $this->factFindingAssignmentTwo->columns['id'] = $this->customerAssignmentTwo->columns['id'];
        $this->factFindingAssignmentTwo->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->factFindingAssignmentTwo->columns['Sales_id'] = $this->salesTwo->columns['id'];
        
        $this->closingRequestByFactFinderOne = new EntityRecord(ClosingRequestByFactFinder::class, 1);
        $this->closingRequestByFactFinderOne->columns['FactFindingAssignment_id'] = $this->factFindingAssignmentOne->columns['id'];
        $this->closingRequestByFactFinderTwo = new EntityRecord(ClosingRequestByFactFinder::class, 2);
        $this->closingRequestByFactFinderTwo->columns['FactFindingAssignment_id'] = $this->factFindingAssignmentTwo->columns['id'];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('FactFindingAssignment')->truncate();
        $this->connection->table('ClosingRequestByFactFinder')->truncate();
    }
    
    //
    protected function accept()
    {
        $this->persistManagerDependency();
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->factFindingAssignmentOne->insert($this->connection);
        $this->closingRequestByFactFinderOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID!, $remark: String ) {
    acceptClosingRequestByFactFinder ( id: $id, remark: $remark ) {
        id, status, transactionValue, note, remark
    }
}
_QUERY;
        $this->graphqlVariables = [
            ...$this->closingRequestByFactFinderPayload,
            'id' => $this->closingRequestByFactFinderOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_accept_200()
    {
$this->disableExceptionHandling();
        $this->accept();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->closingRequestByFactFinderOne->columns['id'],
            'status' => ManagementApprovalStatus::APPROVED->value,
            'transactionValue' => $this->closingRequestByFactFinderOne->columns['transactionValue'],
            'note' => $this->closingRequestByFactFinderOne->columns['note'],
            'remark' => $this->closingRequestByFactFinderPayload['remark'],
        ]);
        
        $this->seeInDatabase('ClosingRequestByFactFinder', [
            'id' => $this->closingRequestByFactFinderOne->columns['id'],
            'status' => ManagementApprovalStatus::APPROVED->value,
            'remark' => $this->closingRequestByFactFinderPayload['remark'],
        ]);
        $this->seeInDatabase('FactFindingAssignment', [
            'id' => $this->factFindingAssignmentOne->columns['id'],
            'status' => CustomerAssignmentStatus::COMPLETED->value,
        ]);
        $this->seeInDatabase('Customer', [
            'id' => $this->customerOne->columns['id'],
            'status' => CustomerStatus::TRANSACTION_BY_FACT_FINDER->value,
        ]);
    }
    
    //
    protected function reject()
    {
        $this->persistManagerDependency();
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->factFindingAssignmentOne->insert($this->connection);
        $this->closingRequestByFactFinderOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $id: ID! ) {
    rejectClosingRequestByFactFinder ( id: $id ) {
        id, status, transactionValue, note
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->closingRequestByFactFinderOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_reject_200()
    {
        $this->reject();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->closingRequestByFactFinderOne->columns['id'],
            'status' => ManagementApprovalStatus::REJECTED->value,
            'transactionValue' => $this->closingRequestByFactFinderOne->columns['transactionValue'],
            'note' => $this->closingRequestByFactFinderOne->columns['note'],
        ]);
        
        $this->seeInDatabase('ClosingRequestByFactFinder', [
            'id' => $this->closingRequestByFactFinderOne->columns['id'],
            'status' => ManagementApprovalStatus::REJECTED->value,
        ]);
        
        $this->seeInDatabase('FactFindingAssignment', [
            'id' => $this->factFindingAssignmentOne->columns['id'],
            'status' => CustomerAssignmentStatus::ACTIVE->value,
        ]);
    }
    
    //
    protected function viewList()
    {
        $this->persistManagerDependency();
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->factFindingAssignmentOne->insert($this->connection);
        $this->closingRequestByFactFinderOne->insert($this->connection);
        
        $this->salesTwo->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        $this->factFindingAssignmentTwo->insert($this->connection);
        $this->closingRequestByFactFinderTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput]) {
    closingRequestByFactFinderList ( filters: $filters) {
        list { 
            id, status, createdTime, transactionValue, note, 
            factFindingAssignment { customer { name }, sales { name } } 
        },
        cursorLimit { total, cursorToNextPage }
    }
}
_QUERY;
        $this->graphqlVariables['filters'] = [
            ['column' => 'ClosingRequestByFactFinder.status', 'value' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value],
        ];
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_viewList_200()
    {
        $this->viewList();
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->closingRequestByFactFinderOne->columns['id'],
                    'status' => $this->closingRequestByFactFinderOne->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->closingRequestByFactFinderOne->columns['createdTime']),
                    'transactionValue' => $this->closingRequestByFactFinderOne->columns['transactionValue'],
                    'note' => $this->closingRequestByFactFinderOne->columns['note'],
                    'factFindingAssignment' => [
                        'customer' => [
                            'name' => $this->customerOne->columns['name'],
                        ],
                        'sales' => [
                                'name' => $this->salesOne->columns['name'],
                        ],
                    ],
                ],
                [
                    'id' => $this->closingRequestByFactFinderTwo->columns['id'],
                    'status' => $this->closingRequestByFactFinderTwo->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->closingRequestByFactFinderTwo->columns['createdTime']),
                    'transactionValue' => $this->closingRequestByFactFinderTwo->columns['transactionValue'],
                    'note' => $this->closingRequestByFactFinderTwo->columns['note'],
                    'factFindingAssignment' => [
                        'customer' => [
                            'name' => $this->customerTwo->columns['name'],
                        ],
                        'sales' => [
                            'name' => $this->salesTwo->columns['name'],
                        ],
                    ],
                ],
            ],
            'cursorLimit' => [
                'total' => 2,
                'cursorToNextPage' => null,
            ],
        ]);
    }
    public function test_viewList_applyFilter()
    {
        $this->closingRequestByFactFinderOne->columns['status'] = ManagementApprovalStatus::REJECTED->value;
        $this->viewList();
        $this->seeJsonDoesntContains(['id' => $this->closingRequestByFactFinderOne->columns['id']]);
        $this->seeJsonContains(['id' => $this->closingRequestByFactFinderTwo->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }
    
    //
    protected function viewDetail()
    {
        $this->persistManagerDependency();
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->factFindingAssignmentOne->insert($this->connection);
        $this->closingRequestByFactFinderOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID!) {
    closingRequestByFactFinderDetail ( id: $id ) {
        id, status, createdTime, transactionValue, note, 
        factFindingAssignment { customer { name }, sales { name } } 
    }
}
_QUERY;
        $this->graphqlVariables['id'] = $this->closingRequestByFactFinderOne->columns['id'];
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeJsonContains([
            'id' => $this->closingRequestByFactFinderOne->columns['id'],
            'status' => $this->closingRequestByFactFinderOne->columns['status'],
            'createdTime' => $this->jakartaDateTimeFormat($this->closingRequestByFactFinderOne->columns['createdTime']),
            'transactionValue' => $this->closingRequestByFactFinderOne->columns['transactionValue'],
            'note' => $this->closingRequestByFactFinderOne->columns['note'],
            'factFindingAssignment' => [
                'customer' => [
                    'name' => $this->customerOne->columns['name'],
                ],
                'sales' => [
                    'name' => $this->salesOne->columns['name'],
                ],
            ],
        ]);
    }
    
    //
    protected function viewClosingRequestByFactFinderCount()
    {
        $this->persistManagerDependency();
        $this->salesOne->insert($this->connection);
        $this->salesTwo->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        $this->factFindingAssignmentOne->insert($this->connection);
        $this->factFindingAssignmentTwo->insert($this->connection);
        $this->closingRequestByFactFinderOne->insert($this->connection);
        $this->closingRequestByFactFinderTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput] ) {
    viewClosingRequestByFactFinderCount ( filters: $filters )
}
_QUERY;
        $this->postGraphqlRequest($this->manager->token);
    }
    public function test_viewClosingRequestByFactFinderCount_200()
    {
$this->disableExceptionHandling();
        $this->viewClosingRequestByFactFinderCount();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['viewClosingRequestByFactFinderCount' => 2]);
    }
    public function test_viewClosingRequestByFactFinderCount_pendingFilter_200()
    {
        $this->closingRequestByFactFinderOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestByFactFinderTwo->columns['status'] = ManagementApprovalStatus::WAITING_FOR_APPROVAL->value;
        $this->graphqlVariables = [
            'filters' => [
                ['column' => 'ClosingRequestByFactFinder.status', 'value' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value],
            ],
        ];
        $this->viewClosingRequestByFactFinderCount();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['viewClosingRequestByFactFinderCount' => 1]);
    }
    public function test_viewClosingRequestByFactFinderCount_completed()
    {
        $this->closingRequestByFactFinderOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestByFactFinderTwo->columns['status'] = ManagementApprovalStatus::REJECTED->value;
        $this->graphqlVariables = [
            'filters' => [
                ['column' => 'ClosingRequestByFactFinder.status', 'value' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value, 'comparisonType' => 'NEQ'],
            ],
        ];
        $this->viewClosingRequestByFactFinderCount();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['viewClosingRequestByFactFinderCount' => 2]);
    }
}
