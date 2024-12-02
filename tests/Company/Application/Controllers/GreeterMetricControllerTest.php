<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\GreeterMetric;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\RecurrenceType;
use Shared\Domain\Enum\SalesMetricType;
use Tests\Company\Application\Controllers\CompanyControllerTestCase;
use Tests\resources\Application\EntityRecord;

class GreeterMetricControllerTest extends CompanyControllerTestCase
{
    protected EntityRecord $greeterMetricOne, $greeterMetricTwo;
    protected $greeterMetricPayload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('GreeterMetric')->truncate();
        //
        $this->greeterMetricOne = new EntityRecord(GreeterMetric::class, 'One');
        $this->greeterMetricTwo = new EntityRecord(GreeterMetric::class, 'Two');
        //
        $this->greeterMetricPayload = [
            'monthlyTarget' => 2250,
            'dailyReminderTarget' => 75,
            'evaluationType' => EvaluationType::COUNT->value,
            'recurrenceType' => RecurrenceType::MONTHLY->value,
            'salesMetricType' => SalesMetricType::SUCCESSFULL_ASSIGNMENT->value,
            'recurrenceCount' => 6,
        ];
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->connection->table('GreeterMetric')->truncate();
    }
    
    //
    protected function createGreeterMetric()
    {
        $this->prepareAdminDependency();
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $monthlyTarget: Int,
    $dailyReminderTarget: Int,
    $evaluationType: String,
    $recurrenceType: String,
    $salesMetricType: String,
    $recurrenceCount: Int,
) {
    createGreeterMetric (
        monthlyTarget: $monthlyTarget,
        dailyReminderTarget: $dailyReminderTarget,
        evaluationType: $evaluationType,
        recurrenceType: $recurrenceType,
        salesMetricType: $salesMetricType,
        recurrenceCount: $recurrenceCount,
    ) {
        id, disabled, monthlyTarget, dailyReminderTarget, evaluationType, recurrenceType, salesMetricType, recurrenceCount,
    }
}
_QUERY;
        $this->graphqlVariables = $this->greeterMetricPayload;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_createGreeterMetric_200()
    {
$this->disableExceptionHandling();
        $this->createGreeterMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'disabled' => false,
            'monthlyTarget' => $this->greeterMetricPayload['monthlyTarget'],
            'dailyReminderTarget' => $this->greeterMetricPayload['dailyReminderTarget'],
            'evaluationType' => $this->greeterMetricPayload['evaluationType'],
            'recurrenceType' => $this->greeterMetricPayload['recurrenceType'],
            'salesMetricType' => $this->greeterMetricPayload['salesMetricType'],
            'recurrenceCount' => $this->greeterMetricPayload['recurrenceCount'],
        ]);
        
        $this->seeInDatabase('GreeterMetric', [
            'disabled' => false,
            'createdTime' => $this->stringOfCurrentTime(),
            'monthlyTarget' => $this->greeterMetricPayload['monthlyTarget'],
            'dailyReminderTarget' => $this->greeterMetricPayload['dailyReminderTarget'],
            'evaluationType' => $this->greeterMetricPayload['evaluationType'],
            'recurrenceType' => $this->greeterMetricPayload['recurrenceType'],
            'salesMetricType' => $this->greeterMetricPayload['salesMetricType'],
            'recurrenceCount' => $this->greeterMetricPayload['recurrenceCount'],
        ]);
    }
    
    //
    protected function updateGreeterMetric()
    {
        $this->prepareAdminDependency();
        $this->greeterMetricOne->insert($this->connection);
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $id: ID,
    $monthlyTarget: Int,
    $dailyReminderTarget: Int,
    $evaluationType: String,
    $recurrenceType: String,
    $salesMetricType: String,
    $recurrenceCount: Int,
) {
    updateGreeterMetric (
        id: $id,
        monthlyTarget: $monthlyTarget,
        dailyReminderTarget: $dailyReminderTarget,
        evaluationType: $evaluationType,
        recurrenceType: $recurrenceType,
        salesMetricType: $salesMetricType,
        recurrenceCount: $recurrenceCount,
    ) {
        monthlyTarget, dailyReminderTarget, evaluationType, recurrenceType, salesMetricType, recurrenceCount,
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->greeterMetricOne->columns['id'],
            ...$this->greeterMetricPayload,
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_updateGreeterMetric_200()
    {
$this->disableExceptionHandling();
        $this->updateGreeterMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'monthlyTarget' => $this->greeterMetricPayload['monthlyTarget'],
            'dailyReminderTarget' => $this->greeterMetricPayload['dailyReminderTarget'],
            'evaluationType' => $this->greeterMetricPayload['evaluationType'],
            'recurrenceType' => $this->greeterMetricPayload['recurrenceType'],
            'salesMetricType' => $this->greeterMetricPayload['salesMetricType'],
            'recurrenceCount' => $this->greeterMetricPayload['recurrenceCount'],
        ]);
        
        $this->seeInDatabase('GreeterMetric', [
            'id' => $this->greeterMetricOne->columns['id'],
            'monthlyTarget' => $this->greeterMetricPayload['monthlyTarget'],
            'dailyReminderTarget' => $this->greeterMetricPayload['dailyReminderTarget'],
            'evaluationType' => $this->greeterMetricPayload['evaluationType'],
            'recurrenceType' => $this->greeterMetricPayload['recurrenceType'],
            'salesMetricType' => $this->greeterMetricPayload['salesMetricType'],
            'recurrenceCount' => $this->greeterMetricPayload['recurrenceCount'],
        ]);
    }
    
    //
    protected function disableGreeterMetric()
    {
        $this->prepareAdminDependency();
        $this->greeterMetricOne->insert($this->connection);
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $id: ID,
) {
    disableGreeterMetric (
        id: $id,
    ) {
        disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->greeterMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_disableGreeterMetric_200()
    {
$this->disableExceptionHandling();
        $this->disableGreeterMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'disabled' => true,
        ]);
        
        $this->seeInDatabase('GreeterMetric', [
            'id' => $this->greeterMetricOne->columns['id'],
            'disabled' => true,
        ]);
    }
    
    //
    protected function enableGreeterMetric()
    {
        $this->prepareAdminDependency();
        $this->greeterMetricOne->columns['disabled'] = true;
        $this->greeterMetricOne->insert($this->connection);
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $id: ID,
) {
    enableGreeterMetric (
        id: $id,
    ) {
        disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->greeterMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_enableGreeterMetric_200()
    {
$this->disableExceptionHandling();
        $this->enableGreeterMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'disabled' => false,
        ]);
        
        $this->seeInDatabase('GreeterMetric', [
            'id' => $this->greeterMetricOne->columns['id'],
            'disabled' => false,
        ]);
    }
    
    //
    protected function viewGreeterMetricDetail()
    {
        $this->prepareAdminDependency();
        $this->greeterMetricOne->insert($this->connection);
        $this->graphqlQuery = <<<'_QUERY'
query (
    $id: ID,
) {
    viewGreeterMetricDetail (
        id: $id,
    ) {
        monthlyTarget, dailyReminderTarget, evaluationType, recurrenceType, salesMetricType, recurrenceCount,
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->greeterMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewGreeterMetricDetail_200()
    {
$this->disableExceptionHandling();
        $this->viewGreeterMetricDetail();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'monthlyTarget' => $this->greeterMetricOne->columns['monthlyTarget'],
            'dailyReminderTarget' => $this->greeterMetricOne->columns['dailyReminderTarget'],
            'evaluationType' => $this->greeterMetricOne->columns['evaluationType'],
            'recurrenceType' => $this->greeterMetricOne->columns['recurrenceType'],
            'salesMetricType' => $this->greeterMetricOne->columns['salesMetricType'],
            'recurrenceCount' => $this->greeterMetricOne->columns['recurrenceCount'],
        ]);
    }
    
    //
    protected function viewGreeterMetricList()
    {
        $this->prepareAdminDependency();
        $this->greeterMetricOne->insert($this->connection);
        $this->greeterMetricTwo->insert($this->connection);
        $this->graphqlQuery = <<<'_QUERY'
query {
    viewGreeterMetricList {
        list {
            id, disabled, monthlyTarget, dailyReminderTarget, evaluationType, recurrenceType, salesMetricType, recurrenceCount 
        },
        cursorLimit { total }
    }
}
_QUERY;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewGreeterMetricList_200()
    {
$this->disableExceptionHandling();
        $this->viewGreeterMetricList();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->greeterMetricOne->columns['id'],
                    'disabled' => $this->greeterMetricOne->columns['disabled'],
                    'monthlyTarget' => $this->greeterMetricOne->columns['monthlyTarget'],
                    'dailyReminderTarget' => $this->greeterMetricOne->columns['dailyReminderTarget'],
                    'evaluationType' => $this->greeterMetricOne->columns['evaluationType'],
                    'recurrenceType' => $this->greeterMetricOne->columns['recurrenceType'],
                    'salesMetricType' => $this->greeterMetricOne->columns['salesMetricType'],
                    'recurrenceCount' => $this->greeterMetricOne->columns['recurrenceCount'],
                ],
                [
                    'id' => $this->greeterMetricTwo->columns['id'],
                    'disabled' => $this->greeterMetricTwo->columns['disabled'],
                    'monthlyTarget' => $this->greeterMetricTwo->columns['monthlyTarget'],
                    'dailyReminderTarget' => $this->greeterMetricTwo->columns['dailyReminderTarget'],
                    'evaluationType' => $this->greeterMetricTwo->columns['evaluationType'],
                    'recurrenceType' => $this->greeterMetricTwo->columns['recurrenceType'],
                    'salesMetricType' => $this->greeterMetricTwo->columns['salesMetricType'],
                    'recurrenceCount' => $this->greeterMetricTwo->columns['recurrenceCount'],
                ],
            ],
            'cursorLimit' => [
                'total' => 2,
            ],
        ]);
    }
}
