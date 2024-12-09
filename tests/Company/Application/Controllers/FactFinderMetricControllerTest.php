<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\FactFinderMetric;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\RecurrenceType;
use Shared\Domain\Enum\SalesMetricType;
use Tests\Company\Application\Controllers\CompanyControllerTestCase;
use Tests\resources\Application\EntityRecord;

class FactFinderMetricControllerTest extends CompanyControllerTestCase
{
    protected EntityRecord $factFinderMetricOne, $factFinderMetricTwo;
    protected $factFinderMetricPayload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('FactFinderMetric')->truncate();
        //
        $this->factFinderMetricOne = new EntityRecord(FactFinderMetric::class, 'One');
        $this->factFinderMetricTwo = new EntityRecord(FactFinderMetric::class, 'Two');
        //
        $this->factFinderMetricPayload = [
            'name' => 'new factFinder metric',
            'target' => 2250,
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
        $this->connection->table('FactFinderMetric')->truncate();
    }
    
    //
    protected function createFactFinderMetric()
    {
        $this->prepareAdminDependency();
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $name: String,
    $target: Int,
    $dailyReminderTarget: Int,
    $evaluationType: String,
    $recurrenceType: String,
    $salesMetricType: String,
    $recurrenceCount: Int,
) {
    createFactFinderMetric (
        name: $name,
        target: $target,
        dailyReminderTarget: $dailyReminderTarget,
        evaluationType: $evaluationType,
        recurrenceType: $recurrenceType,
        salesMetricType: $salesMetricType,
        recurrenceCount: $recurrenceCount,
    ) {
        id, disabled, name, target, dailyReminderTarget, evaluationType, recurrenceType, salesMetricType, recurrenceCount,
    }
}
_QUERY;
        $this->graphqlVariables = $this->factFinderMetricPayload;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_createFactFinderMetric_200()
    {
$this->disableExceptionHandling();
        $this->createFactFinderMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'disabled' => false,
            'name' => $this->factFinderMetricPayload['name'],
            'target' => $this->factFinderMetricPayload['target'],
            'dailyReminderTarget' => $this->factFinderMetricPayload['dailyReminderTarget'],
            'evaluationType' => $this->factFinderMetricPayload['evaluationType'],
            'recurrenceType' => $this->factFinderMetricPayload['recurrenceType'],
            'salesMetricType' => $this->factFinderMetricPayload['salesMetricType'],
            'recurrenceCount' => $this->factFinderMetricPayload['recurrenceCount'],
        ]);
        
        $this->seeInDatabase('FactFinderMetric', [
            'disabled' => false,
            'createdTime' => $this->stringOfCurrentTime(),
            'name' => $this->factFinderMetricPayload['name'],
            'target' => $this->factFinderMetricPayload['target'],
            'dailyReminderTarget' => $this->factFinderMetricPayload['dailyReminderTarget'],
            'evaluationType' => $this->factFinderMetricPayload['evaluationType'],
            'recurrenceType' => $this->factFinderMetricPayload['recurrenceType'],
            'salesMetricType' => $this->factFinderMetricPayload['salesMetricType'],
            'recurrenceCount' => $this->factFinderMetricPayload['recurrenceCount'],
        ]);
    }
    
    //
    protected function updateFactFinderMetric()
    {
        $this->prepareAdminDependency();
        $this->factFinderMetricOne->insert($this->connection);
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $id: ID,
    $name: String,
    $target: Int,
    $dailyReminderTarget: Int,
    $evaluationType: String,
    $recurrenceType: String,
    $salesMetricType: String,
    $recurrenceCount: Int,
) {
    updateFactFinderMetric (
        id: $id,
        name: $name,
        target: $target,
        dailyReminderTarget: $dailyReminderTarget,
        evaluationType: $evaluationType,
        recurrenceType: $recurrenceType,
        salesMetricType: $salesMetricType,
        recurrenceCount: $recurrenceCount,
    ) {
        target, dailyReminderTarget, evaluationType, recurrenceType, salesMetricType, recurrenceCount,
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->factFinderMetricOne->columns['id'],
            ...$this->factFinderMetricPayload,
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_updateFactFinderMetric_200()
    {
$this->disableExceptionHandling();
        $this->updateFactFinderMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'target' => $this->factFinderMetricPayload['target'],
            'dailyReminderTarget' => $this->factFinderMetricPayload['dailyReminderTarget'],
            'evaluationType' => $this->factFinderMetricPayload['evaluationType'],
            'recurrenceType' => $this->factFinderMetricPayload['recurrenceType'],
            'salesMetricType' => $this->factFinderMetricPayload['salesMetricType'],
            'recurrenceCount' => $this->factFinderMetricPayload['recurrenceCount'],
        ]);
        
        $this->seeInDatabase('FactFinderMetric', [
            'id' => $this->factFinderMetricOne->columns['id'],
            'target' => $this->factFinderMetricPayload['target'],
            'dailyReminderTarget' => $this->factFinderMetricPayload['dailyReminderTarget'],
            'evaluationType' => $this->factFinderMetricPayload['evaluationType'],
            'recurrenceType' => $this->factFinderMetricPayload['recurrenceType'],
            'salesMetricType' => $this->factFinderMetricPayload['salesMetricType'],
            'recurrenceCount' => $this->factFinderMetricPayload['recurrenceCount'],
        ]);
    }
    
    //
    protected function disableFactFinderMetric()
    {
        $this->prepareAdminDependency();
        $this->factFinderMetricOne->insert($this->connection);
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $id: ID,
) {
    disableFactFinderMetric (
        id: $id,
    ) {
        disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->factFinderMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_disableFactFinderMetric_200()
    {
$this->disableExceptionHandling();
        $this->disableFactFinderMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'disabled' => true,
        ]);
        
        $this->seeInDatabase('FactFinderMetric', [
            'id' => $this->factFinderMetricOne->columns['id'],
            'disabled' => true,
        ]);
    }
    
    //
    protected function enableFactFinderMetric()
    {
        $this->prepareAdminDependency();
        $this->factFinderMetricOne->columns['disabled'] = true;
        $this->factFinderMetricOne->insert($this->connection);
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $id: ID,
) {
    enableFactFinderMetric (
        id: $id,
    ) {
        disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->factFinderMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_enableFactFinderMetric_200()
    {
$this->disableExceptionHandling();
        $this->enableFactFinderMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'disabled' => false,
        ]);
        
        $this->seeInDatabase('FactFinderMetric', [
            'id' => $this->factFinderMetricOne->columns['id'],
            'disabled' => false,
        ]);
    }
    
    //
    protected function viewFactFinderMetricDetail()
    {
        $this->prepareAdminDependency();
        $this->factFinderMetricOne->insert($this->connection);
        $this->graphqlQuery = <<<'_QUERY'
query (
    $id: ID,
) {
    viewFactFinderMetricDetail (
        id: $id,
    ) {
        target, dailyReminderTarget, evaluationType, recurrenceType, salesMetricType, recurrenceCount,
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->factFinderMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewFactFinderMetricDetail_200()
    {
$this->disableExceptionHandling();
        $this->viewFactFinderMetricDetail();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'target' => $this->factFinderMetricOne->columns['target'],
            'dailyReminderTarget' => $this->factFinderMetricOne->columns['dailyReminderTarget'],
            'evaluationType' => $this->factFinderMetricOne->columns['evaluationType'],
            'recurrenceType' => $this->factFinderMetricOne->columns['recurrenceType'],
            'salesMetricType' => $this->factFinderMetricOne->columns['salesMetricType'],
            'recurrenceCount' => $this->factFinderMetricOne->columns['recurrenceCount'],
        ]);
    }
    
    //
    protected function viewFactFinderMetricList()
    {
        $this->prepareAdminDependency();
        $this->factFinderMetricOne->insert($this->connection);
        $this->factFinderMetricTwo->insert($this->connection);
        $this->graphqlQuery = <<<'_QUERY'
query {
    viewFactFinderMetricList {
        list {
            id, disabled, target, dailyReminderTarget, evaluationType, recurrenceType, salesMetricType, recurrenceCount 
        },
        cursorLimit { total }
    }
}
_QUERY;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewFactFinderMetricList_200()
    {
$this->disableExceptionHandling();
        $this->viewFactFinderMetricList();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->factFinderMetricOne->columns['id'],
                    'disabled' => $this->factFinderMetricOne->columns['disabled'],
                    'target' => $this->factFinderMetricOne->columns['target'],
                    'dailyReminderTarget' => $this->factFinderMetricOne->columns['dailyReminderTarget'],
                    'evaluationType' => $this->factFinderMetricOne->columns['evaluationType'],
                    'recurrenceType' => $this->factFinderMetricOne->columns['recurrenceType'],
                    'salesMetricType' => $this->factFinderMetricOne->columns['salesMetricType'],
                    'recurrenceCount' => $this->factFinderMetricOne->columns['recurrenceCount'],
                ],
                [
                    'id' => $this->factFinderMetricTwo->columns['id'],
                    'disabled' => $this->factFinderMetricTwo->columns['disabled'],
                    'target' => $this->factFinderMetricTwo->columns['target'],
                    'dailyReminderTarget' => $this->factFinderMetricTwo->columns['dailyReminderTarget'],
                    'evaluationType' => $this->factFinderMetricTwo->columns['evaluationType'],
                    'recurrenceType' => $this->factFinderMetricTwo->columns['recurrenceType'],
                    'salesMetricType' => $this->factFinderMetricTwo->columns['salesMetricType'],
                    'recurrenceCount' => $this->factFinderMetricTwo->columns['recurrenceCount'],
                ],
            ],
            'cursorLimit' => [
                'total' => 2,
            ],
        ]);
    }
}
