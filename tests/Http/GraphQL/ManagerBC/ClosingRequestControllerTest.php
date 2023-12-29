<?php

namespace Tests\Http\GraphQL\ManagerBC;

use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager\Sales;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\ClosingRequest;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use SharedContext\Domain\Enum\CustomerAssignmentStatus;
use SharedContext\Domain\Enum\ManagementApprovalStatus;
use Tests\Http\Record\EntityRecord;

class ClosingRequestControllerTest extends ManagerBCTestCase
{
    protected $personnelOne;
    protected $personnelTwo;
    
    protected $salesOne;
    protected $salesTwo;
    
    protected $customerOne;
    protected $customerTwo;

    protected $customerAssignmentOne;
    protected $customerAssignmentTwo;

    protected $closingRequestOne;
    protected $closingRequestTwo;
    protected $closingRequestMonthCurrentOne;
    protected $closingRequestMonthCurrentTwo;
    protected $closingRequestMonthMinOneOne;
    protected $closingRequestMonthMinOneTwo;
    protected $closingRequestMonthMinOneThree;
    protected $closingRequestMonthMinTwoOne;
    protected $closingRequestMonthMinThreeOne;
    protected $closingRequestMonthMinFourOne;
    protected $closingRequestMonthMinFourTwo;
    protected $closingRequestMonthMinFiveOne;
    protected $closingRequestMonthMinSixOne;
    protected $closingRequestMonthMinSixTwo;
    protected $closingRequestMonthMinEighxOne;
    protected $closingRequestMonthMinNineOne;
    protected $closingRequestMonthMinElevenxOne;
    protected $closingRequestMonthMinTwelveOne;
    protected $closingRequestMonthMinThirteenOne;


    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        $this->connection->table('ClosingRequest')->truncate();
        
        $this->personnelOne = new EntityRecord(Personnel::class, 1);
        $this->personnelTwo = new EntityRecord(Personnel::class, 2);
        
        $this->salesOne = new EntityRecord(Sales::class, 1);
        $this->salesOne->columns['Personnel_id'] = $this->personnelOne->columns['id'];
        $this->salesOne->columns['Manager_id'] = $this->manager->columns['id'];
        $this->salesTwo = new EntityRecord(Sales::class, 2);
        $this->salesTwo->columns['Personnel_id'] = $this->personnelTwo->columns['id'];
        $this->salesTwo->columns['Manager_id'] = $this->manager->columns['id'];
        
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        
        $this->customerAssignmentOne = new EntityRecord(AssignedCustomer::class, 1);
        $this->customerAssignmentOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->customerAssignmentOne->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->customerAssignmentTwo = new EntityRecord(AssignedCustomer::class, 2);
        $this->customerAssignmentTwo->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->customerAssignmentTwo->columns['Sales_id'] = $this->salesTwo->columns['id'];
        
        $this->closingRequestOne = new EntityRecord(ClosingRequest::class, 1);
        $this->closingRequestOne->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->closingRequestTwo = new EntityRecord(ClosingRequest::class, 2);
        $this->closingRequestTwo->columns['AssignedCustomer_id'] = $this->customerAssignmentTwo->columns['id'];
        
        $this->closingRequestMonthCurrentOne = new EntityRecord(ClosingRequest::class, '01');
        $this->closingRequestMonthCurrentOne->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->closingRequestMonthCurrentOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthCurrentOne->columns['transactionValue'] = 1000000;
        $this->closingRequestMonthCurrentOne->columns['createdTime'] = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $this->closingRequestMonthCurrentTwo = new EntityRecord(ClosingRequest::class, '02');
        $this->closingRequestMonthCurrentTwo->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->closingRequestMonthCurrentTwo->columns['status'] = ManagementApprovalStatus::REJECTED->value;
        $this->closingRequestMonthCurrentTwo->columns['transactionValue'] = 2000000;
        $this->closingRequestMonthCurrentTwo->columns['createdTime'] = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinOneOne = new EntityRecord(ClosingRequest::class, '11');
        $this->closingRequestMonthMinOneOne->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->closingRequestMonthMinOneOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinOneOne->columns['transactionValue'] = 11000000;
        $this->closingRequestMonthMinOneOne->columns['createdTime'] = (new \DateTimeImmutable('-1 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinOneTwo = new EntityRecord(ClosingRequest::class, '12');
        $this->closingRequestMonthMinOneTwo->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->closingRequestMonthMinOneTwo->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinOneTwo->columns['transactionValue'] = 12000000;
        $this->closingRequestMonthMinOneTwo->columns['createdTime'] = (new \DateTimeImmutable('-1 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinOneThree = new EntityRecord(ClosingRequest::class, '13');
        $this->closingRequestMonthMinOneThree->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->closingRequestMonthMinOneThree->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinOneThree->columns['transactionValue'] = 13000000;
        $this->closingRequestMonthMinOneThree->columns['createdTime'] = (new \DateTimeImmutable('-1 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinTwoOne = new EntityRecord(ClosingRequest::class, '21');
        $this->closingRequestMonthMinTwoOne->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->closingRequestMonthMinTwoOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinTwoOne->columns['transactionValue'] = 21000000;
        $this->closingRequestMonthMinTwoOne->columns['createdTime'] = (new \DateTimeImmutable('-2 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinThreeOne = new EntityRecord(ClosingRequest::class, '31');
        $this->closingRequestMonthMinThreeOne->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->closingRequestMonthMinThreeOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinThreeOne->columns['transactionValue'] = 31000000;
        $this->closingRequestMonthMinThreeOne->columns['createdTime'] = (new \DateTimeImmutable('-3 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinFourOne = new EntityRecord(ClosingRequest::class, '41');
        $this->closingRequestMonthMinFourOne->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->closingRequestMonthMinFourOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinFourOne->columns['transactionValue'] = 41000000;
        $this->closingRequestMonthMinFourOne->columns['createdTime'] = (new \DateTimeImmutable('-4 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinFourTwo = new EntityRecord(ClosingRequest::class, '42');
        $this->closingRequestMonthMinFourTwo->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->closingRequestMonthMinFourTwo->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinFourTwo->columns['transactionValue'] = 42000000;
        $this->closingRequestMonthMinFourTwo->columns['createdTime'] = (new \DateTimeImmutable('-4 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinFiveOne = new EntityRecord(ClosingRequest::class, '51');
        $this->closingRequestMonthMinFiveOne->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->closingRequestMonthMinFiveOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinFiveOne->columns['transactionValue'] = 51000000;
        $this->closingRequestMonthMinFiveOne->columns['createdTime'] = (new \DateTimeImmutable('-5 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinSixOne = new EntityRecord(ClosingRequest::class, '61');
        $this->closingRequestMonthMinSixOne->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->closingRequestMonthMinSixOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinSixOne->columns['transactionValue'] = 61000000;
        $this->closingRequestMonthMinSixOne->columns['createdTime'] = (new \DateTimeImmutable('-6 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinSixTwo = new EntityRecord(ClosingRequest::class, '62');
        $this->closingRequestMonthMinSixTwo->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->closingRequestMonthMinSixTwo->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinSixTwo->columns['transactionValue'] = 62000000;
        $this->closingRequestMonthMinSixTwo->columns['createdTime'] = (new \DateTimeImmutable('-6 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinEighxOne = new EntityRecord(ClosingRequest::class, '81');
        $this->closingRequestMonthMinEighxOne->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->closingRequestMonthMinEighxOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinEighxOne->columns['transactionValue'] = 81000000;
        $this->closingRequestMonthMinEighxOne->columns['createdTime'] = (new \DateTimeImmutable('-8 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinNineOne = new EntityRecord(ClosingRequest::class, '91');
        $this->closingRequestMonthMinNineOne->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->closingRequestMonthMinNineOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinNineOne->columns['transactionValue'] = 91000000;
        $this->closingRequestMonthMinNineOne->columns['createdTime'] = (new \DateTimeImmutable('-9 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinElevenxOne = new EntityRecord(ClosingRequest::class, '111');
        $this->closingRequestMonthMinElevenxOne->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->closingRequestMonthMinElevenxOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinElevenxOne->columns['transactionValue'] = 111000000;
        $this->closingRequestMonthMinElevenxOne->columns['createdTime'] = (new \DateTimeImmutable('-11 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinTwelveOne = new EntityRecord(ClosingRequest::class, '121');
        $this->closingRequestMonthMinTwelveOne->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->closingRequestMonthMinTwelveOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinTwelveOne->columns['transactionValue'] = 121000000;
        $this->closingRequestMonthMinTwelveOne->columns['createdTime'] = (new \DateTimeImmutable('-12 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinThirteenOne = new EntityRecord(ClosingRequest::class, '131');
        $this->closingRequestMonthMinThirteenOne->columns['AssignedCustomer_id'] = $this->customerAssignmentOne->columns['id'];
        $this->closingRequestMonthMinThirteenOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinThirteenOne->columns['transactionValue'] = 131000000;
        $this->closingRequestMonthMinThirteenOne->columns['createdTime'] = (new \DateTimeImmutable('-13 months'))->format('Y-m-d H:i:s');
        
    }
    protected function tearDown(): void
    {
//        parent::tearDown();
//        $this->connection->table('Sales')->truncate();
//        $this->connection->table('Customer')->truncate();
//        $this->connection->table('AssignedCustomer')->truncate();
//        $this->connection->table('ClosingRequest')->truncate();
    }
    
    //
    protected function accept()
    {
        $this->prepareManagerDependency();
        $this->personnelOne->insert($this->connection);
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->closingRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $managerId: ID!, $id: ID! ) {
    manager ( managerId: $managerId ) {
        acceptClosingRequest ( id: $id ) {
            id, status, transactionValue, note
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'managerId' => $this->manager->columns['id'],
            'id' => $this->closingRequestOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_accept_200()
    {
        $this->accept();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->closingRequestOne->columns['id'],
            'status' => ManagementApprovalStatus::APPROVED->value,
            'transactionValue' => $this->closingRequestOne->columns['transactionValue'],
            'note' => $this->closingRequestOne->columns['note'],
        ]);
        
        $this->seeInDatabase('ClosingRequest', [
            'id' => $this->closingRequestOne->columns['id'],
            'status' => ManagementApprovalStatus::APPROVED->value,
        ]);
        $this->seeInDatabase('AssignedCustomer', [
            'id' => $this->customerAssignmentOne->columns['id'],
            'status' => CustomerAssignmentStatus::GOOD_FUND->value,
        ]);
    }
    
    //
    protected function reject()
    {
        $this->prepareManagerDependency();
        $this->personnelOne->insert($this->connection);
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->closingRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $managerId: ID!, $id: ID! ) {
    manager ( managerId: $managerId ) {
        rejectClosingRequest ( id: $id ) {
            id, status, transactionValue, note
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'managerId' => $this->manager->columns['id'],
            'id' => $this->closingRequestOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_reject_200()
    {
        $this->reject();
        $this->seeStatusCode(200);
        $this->seeJsonContains([
            'id' => $this->closingRequestOne->columns['id'],
            'status' => ManagementApprovalStatus::REJECTED->value,
            'transactionValue' => $this->closingRequestOne->columns['transactionValue'],
            'note' => $this->closingRequestOne->columns['note'],
        ]);
        
        $this->seeInDatabase('ClosingRequest', [
            'id' => $this->closingRequestOne->columns['id'],
            'status' => ManagementApprovalStatus::REJECTED->value,
        ]);
        
        $this->seeInDatabase('AssignedCustomer', [
            'id' => $this->customerAssignmentOne->columns['id'],
            'status' => CustomerAssignmentStatus::ACTIVE->value,
        ]);
    }
    
    //
    protected function viewList()
    {
        $this->prepareManagerDependency();
        $this->personnelOne->insert($this->connection);
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->closingRequestOne->insert($this->connection);
        
        $this->personnelTwo->insert($this->connection);
        $this->salesTwo->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        $this->closingRequestTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $managerId: ID!, $filters: [FilterInput]) {
    manager ( managerId: $managerId ) {
        closingRequestList ( filters: $filters) {
            list { 
                id, status, createdTime, transactionValue, note, 
                assignedCustomer { customer { name }, sales { personnel { name } } } 
            },
            cursorLimit { total, cursorToNextPage }
        }
    }
}
_QUERY;
        $this->graphqlVariables['managerId'] = $this->manager->columns['id'];
        $this->graphqlVariables['filters'] = [
            ['column' => 'ClosingRequest.status', 'value' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value],
        ];
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_viewList_200()
    {
        $this->viewList();
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->closingRequestOne->columns['id'],
                    'status' => $this->closingRequestOne->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->closingRequestOne->columns['createdTime']),
                    'transactionValue' => $this->closingRequestOne->columns['transactionValue'],
                    'note' => $this->closingRequestOne->columns['note'],
                    'assignedCustomer' => [
                        'customer' => [
                            'name' => $this->customerOne->columns['name'],
                        ],
                        'sales' => [
                            'personnel' => [
                                'name' => $this->personnelOne->columns['name'],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => $this->closingRequestTwo->columns['id'],
                    'status' => $this->closingRequestTwo->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->closingRequestTwo->columns['createdTime']),
                    'transactionValue' => $this->closingRequestTwo->columns['transactionValue'],
                    'note' => $this->closingRequestTwo->columns['note'],
                    'assignedCustomer' => [
                        'customer' => [
                            'name' => $this->customerTwo->columns['name'],
                        ],
                        'sales' => [
                            'personnel' => [
                                'name' => $this->personnelTwo->columns['name'],
                            ],
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
        $this->closingRequestOne->columns['status'] = ManagementApprovalStatus::REJECTED->value;
        $this->viewList();
        $this->seeJsonDoesntContains(['id' => $this->closingRequestOne->columns['id']]);
        $this->seeJsonContains(['id' => $this->closingRequestTwo->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }
    
    //
    protected function viewDetail()
    {
        $this->prepareManagerDependency();
        $this->personnelOne->insert($this->connection);
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->closingRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $managerId: ID!, $id: ID!) {
    manager ( managerId: $managerId ) {
        closingRequestDetail ( id: $id ) {
            id, status, createdTime, transactionValue, note, 
            assignedCustomer { customer { name }, sales { personnel { name } } } 
        }
    }
}
_QUERY;
        $this->graphqlVariables['managerId'] = $this->manager->columns['id'];
        $this->graphqlVariables['id'] = $this->closingRequestOne->columns['id'];
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeJsonContains([
            'id' => $this->closingRequestOne->columns['id'],
            'status' => $this->closingRequestOne->columns['status'],
            'createdTime' => $this->jakartaDateTimeFormat($this->closingRequestOne->columns['createdTime']),
            'transactionValue' => $this->closingRequestOne->columns['transactionValue'],
            'note' => $this->closingRequestOne->columns['note'],
            'assignedCustomer' => [
                'customer' => [
                    'name' => $this->customerOne->columns['name'],
                ],
                'sales' => [
                    'personnel' => [
                        'name' => $this->personnelOne->columns['name'],
                    ],
                ],
            ],
        ]);
    }
    
    //
    protected function monthlyTotalTransaction()
    {
        $this->prepareManagerDependency();
        $this->customerOne->insert($this->connection);
        $this->personnelOne->insert($this->connection);
        $this->salesOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        
        $this->closingRequestMonthCurrentOne->insert($this->connection);
        $this->closingRequestMonthCurrentTwo->insert($this->connection);
        $this->closingRequestMonthMinOneOne->insert($this->connection);
        $this->closingRequestMonthMinOneTwo->insert($this->connection);
        $this->closingRequestMonthMinOneThree->insert($this->connection);
        $this->closingRequestMonthMinTwoOne->insert($this->connection);
        $this->closingRequestMonthMinThreeOne->insert($this->connection);
        $this->closingRequestMonthMinFourOne->insert($this->connection);
        $this->closingRequestMonthMinFourTwo->insert($this->connection);
        $this->closingRequestMonthMinFiveOne->insert($this->connection);
        $this->closingRequestMonthMinSixOne->insert($this->connection);
        $this->closingRequestMonthMinSixTwo->insert($this->connection);
        $this->closingRequestMonthMinEighxOne->insert($this->connection);
        $this->closingRequestMonthMinNineOne->insert($this->connection);
        $this->closingRequestMonthMinElevenxOne->insert($this->connection);
        $this->closingRequestMonthMinTwelveOne->insert($this->connection);
        $this->closingRequestMonthMinThirteenOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $managerId: ID! ) {
    manager ( managerId: $managerId ) {
        monthlyTotalTransaction {
            yearMonth, totalTransaction
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'managerId' => $this->manager->columns['id'],
        ];
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_monthlyTotalTransaction_200()
    {
        $this->monthlyTotalTransaction();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['yearMonth' => (new \DateTime())->format('Ym'), 'totalTransaction' => 1000000]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-1  months'))->format('Ym'), 'totalTransaction' => 36000000]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-2  months'))->format('Ym'), 'totalTransaction' => 21000000]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-3  months'))->format('Ym'), 'totalTransaction' => 31000000]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-4  months'))->format('Ym'), 'totalTransaction' => 83000000]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-5  months'))->format('Ym'), 'totalTransaction' => 51000000]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-6  months'))->format('Ym'), 'totalTransaction' => 123000000]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-8  months'))->format('Ym'), 'totalTransaction' => 81000000]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-9  months'))->format('Ym'), 'totalTransaction' => 91000000]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-11  months'))->format('Ym'), 'totalTransaction' => 111000000]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-12  months'))->format('Ym'), 'totalTransaction' => 121000000]);
    }
    //
    protected function monthlyTransactionCount()
    {
        $this->prepareManagerDependency();
        $this->customerOne->insert($this->connection);
        $this->personnelOne->insert($this->connection);
        $this->salesOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        
        $this->closingRequestMonthCurrentOne->insert($this->connection);
        $this->closingRequestMonthCurrentTwo->insert($this->connection);
        $this->closingRequestMonthMinOneOne->insert($this->connection);
        $this->closingRequestMonthMinOneTwo->insert($this->connection);
        $this->closingRequestMonthMinOneThree->insert($this->connection);
        $this->closingRequestMonthMinTwoOne->insert($this->connection);
        $this->closingRequestMonthMinThreeOne->insert($this->connection);
        $this->closingRequestMonthMinFourOne->insert($this->connection);
        $this->closingRequestMonthMinFourTwo->insert($this->connection);
        $this->closingRequestMonthMinFiveOne->insert($this->connection);
        $this->closingRequestMonthMinSixOne->insert($this->connection);
        $this->closingRequestMonthMinSixTwo->insert($this->connection);
        $this->closingRequestMonthMinEighxOne->insert($this->connection);
        $this->closingRequestMonthMinNineOne->insert($this->connection);
        $this->closingRequestMonthMinElevenxOne->insert($this->connection);
        $this->closingRequestMonthMinTwelveOne->insert($this->connection);
        $this->closingRequestMonthMinThirteenOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $managerId: ID! ) {
    manager ( managerId: $managerId ) {
        monthlyTransactionCount {
            yearMonth, closingCount
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'managerId' => $this->manager->columns['id'],
        ];
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_monthlyTransactionCount_200()
    {
        $this->monthlyTransactionCount();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['yearMonth' => (new \DateTime())->format('Ym'), 'closingCount' => 1]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-1  months'))->format('Ym'), 'closingCount' => 3]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-2  months'))->format('Ym'), 'closingCount' => 1]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-3  months'))->format('Ym'), 'closingCount' => 1]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-4  months'))->format('Ym'), 'closingCount' => 2]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-5  months'))->format('Ym'), 'closingCount' => 1]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-6  months'))->format('Ym'), 'closingCount' => 2]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-8  months'))->format('Ym'), 'closingCount' => 1]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-9  months'))->format('Ym'), 'closingCount' => 1]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-11  months'))->format('Ym'), 'closingCount' => 1]);
        $this->seeJsonContains(['yearMonth' => (new \DateTime('-12  months'))->format('Ym'), 'closingCount' => 1]);
    }
}
