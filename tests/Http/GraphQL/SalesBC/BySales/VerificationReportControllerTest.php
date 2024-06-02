<?php

namespace App\Http\Controllers\SalesBC\BySales;

use Company\Domain\Model\AreaStructure\Area\Customer;
use Company\Domain\Model\CustomerVerification;
use Sales\Domain\DependencyModel\AreaStructure\Area\Customer\VerificationReport;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Tests\Http\GraphQL\SalesBC\SalesBCTestCase;
use Tests\Http\Record\EntityRecord;

class VerificationReportControllerTest extends SalesBCTestCase
{
    protected $customerVerification;
    protected $customerVerificationOne;
    protected $customerVerificationTwo;
    
    protected $customer;
    protected $customerAssignment;
    
    protected $verificationReportOne;
    protected $verificationReportTwo;
    
    protected $submitReportRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('CustomerVerification')->truncate();
        $this->connection->table('Customer')->truncate();
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('VerificationReport')->truncate();
        
        $this->customerVerification = new EntityRecord(CustomerVerification::class, 'main');
        $this->customerVerificationOne = new EntityRecord(CustomerVerification::class, 1);
        $this->customerVerificationTwo = new EntityRecord(CustomerVerification::class, 2);
        
        $this->customer = new EntityRecord(Customer::class, 'main');
        $this->customerAssignment = new EntityRecord(CustomerAssignment::class, 'main');
        $this->customerAssignment->columns['Customer_id'] = $this->customer->columns['id'];
        $this->customerAssignment->columns['Sales_id'] = $this->sales->columns['id'];
        
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
        $this->connection->table('CustomerAssignment')->truncate();
        $this->connection->table('VerificationReport')->truncate();
    }
    
    //
    protected function submitReport()
    {
        $this->prepareSalesDependency();
        $this->customerVerification->insert($this->connection);
        $this->customer->insert($this->connection);
        $this->customerAssignment->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
mutation ( $CustomerAssignment_id: ID!, $CustomerVerification_id: ID!, $note: String ) {
    submitCustomerVerificationReport ( CustomerAssignment_id: $CustomerAssignment_id, CustomerVerification_id: $CustomerVerification_id, note: $note) {
        createdTime, note, CustomerVerification_id
    }
}
_QUERY;
        $this->graphqlVariables = [
            'CustomerAssignment_id' => $this->customerAssignment->columns['id'],
            'CustomerVerification_id' => $this->customerVerification->columns['id'],
            ...$this->submitReportRequest
        ];
        $this->postGraphqlRequest($this->sales->token);
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
        $this->customerAssignment->insert($this->connection);
        
        $this->verificationReportOne->insert($this->connection);
        $this->verificationReportTwo->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $filters: [FilterInput]) {
    verificationReportList (filters: $filters) {
        list { id, createdTime, note, CustomerVerification_id },
        cursorLimit { total, cursorToNextPage }
    }
}
_QUERY;
        $this->graphqlVariables['filters'] = [
            ['column' => 'CustomerAssignment.id', 'value' => $this->customerAssignment->columns['id']],
        ];
        $this->postGraphqlRequest($this->sales->token);
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
    public function test_viewList_userCustomerAssignmentFilter()
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
        $this->customerAssignment->insert($this->connection);
        
        $this->verificationReportOne->insert($this->connection);
        
        $this->graphqlQuery = <<<'_QUERY'
query ( $id: ID!) {
    verificationReportDetail ( id: $id ) {
        id, note, createdTime, customerVerification { id, name }
    }
}
_QUERY;
        $this->graphqlVariables['id'] = $this->verificationReportOne->columns['id'];
        $this->postGraphqlRequest($this->sales->token);
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
