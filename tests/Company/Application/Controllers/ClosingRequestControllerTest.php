<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\Customer;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Domain\Model\Manager\Sales\StrikingAssignment;
use Company\Domain\Model\Manager\Sales\StrikingAssignment\ClosingRequest;
use DateTime;
use DateTimeImmutable;
use Shared\Domain\Enum\ManagementApprovalStatus;
use Tests\Company\Application\Controllers\CompanyControllerTestCase;
use Tests\resources\Application\EntityRecord;


class ClosingRequestControllerTest extends CompanyControllerTestCase
{
    protected $salesOne;
    protected $salesTwo;
    
    protected $customerOne;
    protected $customerTwo;

    protected $strikingAssignmentOne, $customerAssignmentOne;
    protected $strikingAssignmentTwo, $customerAssignmentTwo;

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
        $this->connection->table('StrikingAssignment')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('ClosingRequest')->truncate();
        
        $this->salesOne = new EntityRecord(Sales::class, 1);
        $this->salesTwo = new EntityRecord(Sales::class, 2);
        
        $this->customerOne = new EntityRecord(Customer::class, 1);
        $this->customerTwo = new EntityRecord(Customer::class, 2);
        
        $this->customerAssignmentOne = new EntityRecord(CustomerAssignment::class, 'One');
        $this->strikingAssignmentOne = new EntityRecord(StrikingAssignment::class, 'One');
        $this->strikingAssignmentOne->columns['CustomerAssignment_id'] = $this->customerAssignmentOne->columns['id'];
        $this->strikingAssignmentOne->columns['id'] = $this->customerAssignmentOne->columns['id'];
        $this->strikingAssignmentOne->columns['Customer_id'] = $this->customerOne->columns['id'];
        $this->strikingAssignmentOne->columns['Sales_id'] = $this->salesOne->columns['id'];
        $this->customerAssignmentTwo = new EntityRecord(CustomerAssignment::class, 'Two');
        $this->strikingAssignmentTwo = new EntityRecord(StrikingAssignment::class, 'Two');
        $this->strikingAssignmentTwo->columns['CustomerAssignment_id'] = $this->customerAssignmentTwo->columns['id'];
        $this->strikingAssignmentTwo->columns['id'] = $this->customerAssignmentTwo->columns['id'];
        $this->strikingAssignmentTwo->columns['Customer_id'] = $this->customerTwo->columns['id'];
        $this->strikingAssignmentTwo->columns['Sales_id'] = $this->salesTwo->columns['id'];
        
        $this->closingRequestOne = new EntityRecord(ClosingRequest::class, 1);
        $this->closingRequestOne->columns['StrikingAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->closingRequestTwo = new EntityRecord(ClosingRequest::class, 2);
        $this->closingRequestTwo->columns['StrikingAssignment_id'] = $this->strikingAssignmentTwo->columns['id'];
        
        $this->closingRequestMonthCurrentOne = new EntityRecord(ClosingRequest::class, '01');
        $this->closingRequestMonthCurrentOne->columns['StrikingAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->closingRequestMonthCurrentOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthCurrentOne->columns['transactionValue'] = 1000000;
        $this->closingRequestMonthCurrentOne->columns['createdTime'] = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $this->closingRequestMonthCurrentTwo = new EntityRecord(ClosingRequest::class, '02');
        $this->closingRequestMonthCurrentTwo->columns['StrikingAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->closingRequestMonthCurrentTwo->columns['status'] = ManagementApprovalStatus::REJECTED->value;
        $this->closingRequestMonthCurrentTwo->columns['transactionValue'] = 2000000;
        $this->closingRequestMonthCurrentTwo->columns['createdTime'] = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinOneOne = new EntityRecord(ClosingRequest::class, '11');
        $this->closingRequestMonthMinOneOne->columns['StrikingAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->closingRequestMonthMinOneOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinOneOne->columns['transactionValue'] = 11000000;
        $this->closingRequestMonthMinOneOne->columns['createdTime'] = (new DateTimeImmutable('-1 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinOneTwo = new EntityRecord(ClosingRequest::class, '12');
        $this->closingRequestMonthMinOneTwo->columns['StrikingAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->closingRequestMonthMinOneTwo->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinOneTwo->columns['transactionValue'] = 12000000;
        $this->closingRequestMonthMinOneTwo->columns['createdTime'] = (new DateTimeImmutable('-1 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinOneThree = new EntityRecord(ClosingRequest::class, '13');
        $this->closingRequestMonthMinOneThree->columns['StrikingAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->closingRequestMonthMinOneThree->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinOneThree->columns['transactionValue'] = 13000000;
        $this->closingRequestMonthMinOneThree->columns['createdTime'] = (new DateTimeImmutable('-1 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinTwoOne = new EntityRecord(ClosingRequest::class, '21');
        $this->closingRequestMonthMinTwoOne->columns['StrikingAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->closingRequestMonthMinTwoOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinTwoOne->columns['transactionValue'] = 21000000;
        $this->closingRequestMonthMinTwoOne->columns['createdTime'] = (new DateTimeImmutable('-2 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinThreeOne = new EntityRecord(ClosingRequest::class, '31');
        $this->closingRequestMonthMinThreeOne->columns['StrikingAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->closingRequestMonthMinThreeOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinThreeOne->columns['transactionValue'] = 31000000;
        $this->closingRequestMonthMinThreeOne->columns['createdTime'] = (new DateTimeImmutable('-3 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinFourOne = new EntityRecord(ClosingRequest::class, '41');
        $this->closingRequestMonthMinFourOne->columns['StrikingAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->closingRequestMonthMinFourOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinFourOne->columns['transactionValue'] = 41000000;
        $this->closingRequestMonthMinFourOne->columns['createdTime'] = (new DateTimeImmutable('-4 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinFourTwo = new EntityRecord(ClosingRequest::class, '42');
        $this->closingRequestMonthMinFourTwo->columns['StrikingAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->closingRequestMonthMinFourTwo->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinFourTwo->columns['transactionValue'] = 42000000;
        $this->closingRequestMonthMinFourTwo->columns['createdTime'] = (new DateTimeImmutable('-4 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinFiveOne = new EntityRecord(ClosingRequest::class, '51');
        $this->closingRequestMonthMinFiveOne->columns['StrikingAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->closingRequestMonthMinFiveOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinFiveOne->columns['transactionValue'] = 51000000;
        $this->closingRequestMonthMinFiveOne->columns['createdTime'] = (new DateTimeImmutable('-5 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinSixOne = new EntityRecord(ClosingRequest::class, '61');
        $this->closingRequestMonthMinSixOne->columns['StrikingAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->closingRequestMonthMinSixOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinSixOne->columns['transactionValue'] = 61000000;
        $this->closingRequestMonthMinSixOne->columns['createdTime'] = (new DateTimeImmutable('-6 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinSixTwo = new EntityRecord(ClosingRequest::class, '62');
        $this->closingRequestMonthMinSixTwo->columns['StrikingAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->closingRequestMonthMinSixTwo->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinSixTwo->columns['transactionValue'] = 62000000;
        $this->closingRequestMonthMinSixTwo->columns['createdTime'] = (new DateTimeImmutable('-6 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinEighxOne = new EntityRecord(ClosingRequest::class, '81');
        $this->closingRequestMonthMinEighxOne->columns['StrikingAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->closingRequestMonthMinEighxOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinEighxOne->columns['transactionValue'] = 81000000;
        $this->closingRequestMonthMinEighxOne->columns['createdTime'] = (new DateTimeImmutable('-8 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinNineOne = new EntityRecord(ClosingRequest::class, '91');
        $this->closingRequestMonthMinNineOne->columns['StrikingAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->closingRequestMonthMinNineOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinNineOne->columns['transactionValue'] = 91000000;
        $this->closingRequestMonthMinNineOne->columns['createdTime'] = (new DateTimeImmutable('-9 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinElevenxOne = new EntityRecord(ClosingRequest::class, '111');
        $this->closingRequestMonthMinElevenxOne->columns['StrikingAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->closingRequestMonthMinElevenxOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinElevenxOne->columns['transactionValue'] = 111000000;
        $this->closingRequestMonthMinElevenxOne->columns['createdTime'] = (new DateTimeImmutable('-11 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinTwelveOne = new EntityRecord(ClosingRequest::class, '121');
        $this->closingRequestMonthMinTwelveOne->columns['StrikingAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->closingRequestMonthMinTwelveOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinTwelveOne->columns['transactionValue'] = 121000000;
        $this->closingRequestMonthMinTwelveOne->columns['createdTime'] = (new DateTimeImmutable('-12 months'))->format('Y-m-d H:i:s');
        $this->closingRequestMonthMinThirteenOne = new EntityRecord(ClosingRequest::class, '131');
        $this->closingRequestMonthMinThirteenOne->columns['StrikingAssignment_id'] = $this->strikingAssignmentOne->columns['id'];
        $this->closingRequestMonthMinThirteenOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthMinThirteenOne->columns['transactionValue'] = 131000000;
        $this->closingRequestMonthMinThirteenOne->columns['createdTime'] = (new DateTimeImmutable('-13 months'))->format('Y-m-d H:i:s');
        
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('Sales')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('StrikingAssignment')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('ClosingRequest')->truncate();
    }
    //
    protected function viewList()
    {
        $this->prepareAdminDependency();
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->strikingAssignmentOne->insert($this->connection);
        $this->closingRequestOne->insert($this->connection);
        
        $this->salesTwo->insert($this->connection);
        $this->customerTwo->insert($this->connection);
        $this->customerAssignmentTwo->insert($this->connection);
        $this->strikingAssignmentTwo->insert($this->connection);
        $this->closingRequestTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput]) {
    closingRequestList ( filters: $filters) {
        list { 
            id, status, createdTime, transactionValue, note, 
            strikingAssignment { customer { name }, sales { name } } 
        },
        cursorLimit { total, cursorToNextPage }
    }
}
_QUERY;
        $this->graphqlVariables['filters'] = [
            ['column' => 'ClosingRequest.status', 'value' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value],
        ];
        $this->postGraphqlRequest($this->admin->token);
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
                    'strikingAssignment' => [
                        'customer' => [
                            'name' => $this->customerOne->columns['name'],
                        ],
                        'sales' => [
                                'name' => $this->salesOne->columns['name'],
                        ],
                    ],
                ],
                [
                    'id' => $this->closingRequestTwo->columns['id'],
                    'status' => $this->closingRequestTwo->columns['status'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->closingRequestTwo->columns['createdTime']),
                    'transactionValue' => $this->closingRequestTwo->columns['transactionValue'],
                    'note' => $this->closingRequestTwo->columns['note'],
                    'strikingAssignment' => [
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
        $this->closingRequestOne->columns['status'] = ManagementApprovalStatus::REJECTED->value;
        $this->viewList();
        $this->seeJsonDoesntContains(['id' => $this->closingRequestOne->columns['id']]);
        $this->seeJsonContains(['id' => $this->closingRequestTwo->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }
    
    //
    protected function viewDetail()
    {
        $this->prepareAdminDependency();
        $this->salesOne->insert($this->connection);
        $this->customerOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->strikingAssignmentOne->insert($this->connection);
        $this->closingRequestOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID!) {
    closingRequestDetail ( id: $id ) {
        id, status, createdTime, transactionValue, note, 
        strikingAssignment { customer { name }, sales { name } } 
    }
}
_QUERY;
        $this->graphqlVariables['id'] = $this->closingRequestOne->columns['id'];
        $this->postGraphqlRequest($this->admin->token);
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
            'strikingAssignment' => [
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
    protected function monthlyTotalTransaction()
    {
        $this->prepareAdminDependency();
        $this->customerOne->insert($this->connection);
        $this->salesOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->strikingAssignmentOne->insert($this->connection);
        
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
query {
    monthlyTotalTransaction {
        yearMonth, totalTransaction
    }
}
_QUERY;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_monthlyTotalTransaction_200()
    {
        $this->monthlyTotalTransaction();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['yearMonth' => (new DateTime())->format('Ym'), 'totalTransaction' => 1000000]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-1  months'))->format('Ym'), 'totalTransaction' => 36000000]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-2  months'))->format('Ym'), 'totalTransaction' => 21000000]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-3  months'))->format('Ym'), 'totalTransaction' => 31000000]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-4  months'))->format('Ym'), 'totalTransaction' => 83000000]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-5  months'))->format('Ym'), 'totalTransaction' => 51000000]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-6  months'))->format('Ym'), 'totalTransaction' => 123000000]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-8  months'))->format('Ym'), 'totalTransaction' => 81000000]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-9  months'))->format('Ym'), 'totalTransaction' => 91000000]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-11  months'))->format('Ym'), 'totalTransaction' => 111000000]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-12  months'))->format('Ym'), 'totalTransaction' => 121000000]);
    }
    //
    protected function monthlyTransactionCount()
    {
        $this->prepareAdminDependency();
        $this->customerOne->insert($this->connection);
        $this->salesOne->insert($this->connection);
        $this->customerAssignmentOne->insert($this->connection);
        $this->strikingAssignmentOne->insert($this->connection);
        
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
query {
    monthlyTransactionCount {
        yearMonth, closingCount
    }
}
_QUERY;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_monthlyTransactionCount_200()
    {
        $this->monthlyTransactionCount();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['yearMonth' => (new DateTime())->format('Ym'), 'closingCount' => 1]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-1  months'))->format('Ym'), 'closingCount' => 3]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-2  months'))->format('Ym'), 'closingCount' => 1]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-3  months'))->format('Ym'), 'closingCount' => 1]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-4  months'))->format('Ym'), 'closingCount' => 2]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-5  months'))->format('Ym'), 'closingCount' => 1]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-6  months'))->format('Ym'), 'closingCount' => 2]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-8  months'))->format('Ym'), 'closingCount' => 1]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-9  months'))->format('Ym'), 'closingCount' => 1]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-11  months'))->format('Ym'), 'closingCount' => 1]);
        $this->seeJsonContains(['yearMonth' => (new DateTime('-12  months'))->format('Ym'), 'closingCount' => 1]);
    }
    
    protected function viewClosingRequestCount()
    {
        $this->prepareAdminDependency();
        $this->closingRequestMonthCurrentOne->insert($this->connection);
        $this->closingRequestMonthCurrentTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput] ) {
    viewClosingRequestCount ( filters: $filters )
}
_QUERY;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewClosingRequestCount_200()
    {
$this->disableExceptionHandling();
        $this->viewClosingRequestCount();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['viewClosingRequestCount' => 2]);
    }
    public function test_viewClosingRequestCount_pendingFilter_200()
    {
        $this->closingRequestMonthCurrentOne->columns['status'] = ManagementApprovalStatus::WAITING_FOR_APPROVAL->value;
        $this->closingRequestMonthCurrentTwo->columns['status'] = ManagementApprovalStatus::REJECTED->value;
        $this->graphqlVariables = [
            'filters' => [
                ['column' => 'ClosingRequest.status', 'value' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value],
            ],
        ];
        $this->viewClosingRequestCount();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['viewClosingRequestCount' => 1]);
    }
    public function test_viewClosingRequestCount_completed()
    {
        $this->closingRequestMonthCurrentOne->columns['status'] = ManagementApprovalStatus::APPROVED->value;
        $this->closingRequestMonthCurrentTwo->columns['status'] = ManagementApprovalStatus::REJECTED->value;
        $this->graphqlVariables = [
            'filters' => [
                ['column' => 'ClosingRequest.status', 'value' => ManagementApprovalStatus::WAITING_FOR_APPROVAL->value, 'comparisonType' => 'NEQ'],
            ],
        ];
        $this->viewClosingRequestCount();
        $this->seeStatusCode(200);
        $this->seeJsonContains(['viewClosingRequestCount' => 2]);
    }
}
