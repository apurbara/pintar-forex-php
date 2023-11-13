<?php

namespace Tests\Http\GraphQL\SalesBC;

use Company\Domain\Model\CustomerVerification;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\AreaStructure\Area\Customer\VerificationReport;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Tests\Http\Record\EntityRecord;

class VerificationReportControllerTest extends SalesBCTestCase
{
    protected $customerVerification;
    protected $customerVerificationOne;
    protected $customerVerificationTwo;
    
    protected $customer;
    protected $assignedCustomer;
    
    protected $verificationReportOne;
    protected $verificationReportTwo;
    
    protected $submitReportRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('CustomerVerification')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        $this->connection->table('VerificationReport')->truncate();
        
        $this->customerVerification = new EntityRecord(CustomerVerification::class, 'main');
        $this->customerVerificationOne = new EntityRecord(CustomerVerification::class, 1);
        $this->customerVerificationTwo = new EntityRecord(CustomerVerification::class, 2);
        
        $this->customer = new EntityRecord(Customer::class, 'main');
        $this->assignedCustomer = new EntityRecord(AssignedCustomer::class, 'main');
        $this->assignedCustomer->columns['Customer_id'] = $this->customer->columns['id'];
        $this->assignedCustomer->columns['Sales_id'] = $this->sales->columns['id'];
        
        $this->verificationReportOne = new EntityRecord(VerificationReport::class, 1);
        $this->verificationReportOne->columns['Customer_id'] = $this->customer->columns['id'];
        $this->verificationReportOne->columns['CustomerVerification_id'] = $this->customerVerificationOne->columns['id'];
        $this->verificationReportTwo = new EntityRecord(VerificationReport::class, 2);
        $this->verificationReportTwo->columns['Customer_id'] = $this->customer->columns['id'];
        $this->verificationReportTwo->columns['CustomerVerification_id'] = $this->customerVerificationTwo->columns['id'];
        
        $this->submitReportRequest = [
            'note' => 'next report content',
        ];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('CustomerVerification')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('AssignedCustomer')->truncate();
        $this->connection->table('VerificationReport')->truncate();
    }
    
    //
    protected function submitReport()
    {
        $this->prepareSalesDependency();
        $this->customerVerification->insert($this->connection);
        $this->customer->insert($this->connection);
        $this->assignedCustomer->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $salesId: ID!, $assignedCustomerId: ID!, $customerVerificationId: ID!, $note: String ) {
    sales ( salesId: $salesId ) {
        assignedCustomer ( assignedCustomerId: $assignedCustomerId ) {
            submitCustomerVerificationReport ( customerVerificationId: $customerVerificationId, note: $note) {
                createdTime, note, CustomerVerification_id
            }
        }
    }
}
_QUERY;
        $this->graphqlVariables = [
            'salesId' => $this->sales->columns['id'],
            'assignedCustomerId' => $this->assignedCustomer->columns['id'],
            'customerVerificationId' => $this->customerVerification->columns['id'],
            ...$this->submitReportRequest
        ];
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_submitReport_200()
    {
        $this->submitReport();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'note' => $this->submitReportRequest['note'],
            'createdTime' => $this->stringOfJakartaCurrentTime(),
            'CustomerVerification_id' => $this->customerVerification->columns['id'],
        ]);
        
        $this->seeInDatabase('VerificationReport', [
            'Customer_id' => $this->customer->columns['id'],
            'CustomerVerification_id' => $this->customerVerification->columns['id'],
            'note' => $this->submitReportRequest['note'],
            'createdTime' => $this->stringOfJakartaCurrentTime(),
        ]);
    }
    public function test_submitReport_alreadyHasReportAssociateToSameVerification_updateExisting()
    {
        $this->verificationReportOne->columns['CustomerVerification_id'] = $this->customerVerification->columns['id'];
        $this->verificationReportOne->insert($this->connection);
        $this->submitReport();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'note' => $this->submitReportRequest['note'],
            'CustomerVerification_id' => $this->customerVerification->columns['id'],
        ]);
        
        $this->seeInDatabase('VerificationReport', [
            'id' => $this->verificationReportOne->columns['id'],
            'Customer_id' => $this->customer->columns['id'],
            'CustomerVerification_id' => $this->customerVerification->columns['id'],
            'note' => $this->submitReportRequest['note'],
        ]);
    }
    
    //
    protected function viewList()
    {
        $this->prepareSalesDependency();
        
        $this->customerVerificationOne->insert($this->connection);
        $this->customerVerificationTwo->insert($this->connection);
        
        $this->customer->insert($this->connection);
        $this->assignedCustomer->insert($this->connection);
        
        $this->verificationReportOne->insert($this->connection);
        $this->verificationReportTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $salesId: ID!, $filters: [FilterInput]) {
    sales ( salesId: $salesId ) {
        verificationReportList (filters: $filters) {
            list { id, createdTime, note, CustomerVerification_id },
            cursorLimit { total, cursorToNextPage }
        }
    }
}
_QUERY;
        $this->graphqlVariables['salesId'] = $this->sales->columns['id'];
        $this->graphqlVariables['filters'] = [
            ['column' => 'AssignedCustomer.id', 'value' => $this->assignedCustomer->columns['id']],
        ];
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_viewList_200()
    {
        $this->viewList();
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->verificationReportOne->columns['id'],
                    'note' => $this->verificationReportOne->columns['note'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->verificationReportOne->columns['createdTime']),
                    'CustomerVerification_id' => $this->customerVerificationOne->columns['id'],
                ],
                [
                    'id' => $this->verificationReportTwo->columns['id'],
                    'note' => $this->verificationReportTwo->columns['note'],
                    'createdTime' => $this->jakartaDateTimeFormat($this->verificationReportTwo->columns['createdTime']),
                    'CustomerVerification_id' => $this->customerVerificationTwo->columns['id'],
                ],
            ],
            'cursorLimit' => [
                'total' => 2,
                'cursorToNextPage' => null,
            ],
        ]);
    }
    public function test_viewList_userAssignedCustomerFilter()
    {
        $this->verificationReportOne->columns['Customer_id'] = 'exclude';
        $this->viewList();
        $this->seeJsonDoesntContains(['id' => $this->verificationReportOne->columns['id']]);
        $this->seeJsonContains(['id' => $this->verificationReportTwo->columns['id']]);
        $this->seeJsonContains(['total' => 1]);
    }
    
    //
    protected function viewDetail()
    {
        $this->prepareSalesDependency();
        $this->customerVerificationOne->insert($this->connection);
        $this->customer->insert($this->connection);
        $this->assignedCustomer->insert($this->connection);
        
        $this->verificationReportOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $salesId: ID!, $id: ID!) {
    sales ( salesId: $salesId ) {
        verificationReportDetail ( verificationReportId: $id ) {
            id, note, createdTime, customerVerification { id, name }
        }
    }
}
_QUERY;
        $this->graphqlVariables['salesId'] = $this->sales->columns['id'];
        $this->graphqlVariables['id'] = $this->verificationReportOne->columns['id'];
        $this->postGraphqlRequest($this->personnel->token);
    }
    public function test_viewDetail_200()
    {
        $this->viewDetail();
        $this->seeJsonContains([
            'id' => $this->verificationReportOne->columns['id'],
            'note' => $this->verificationReportOne->columns['note'],
            'createdTime' => $this->jakartaDateTimeFormat($this->verificationReportOne->columns['createdTime']),
            'customerVerification' => [
                'id' => $this->customerVerificationOne->columns['id'],
                'name' => $this->customerVerificationOne->columns['name'],
            ],
        ]);
    }
}
