<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\StrikerMetric;
use Shared\Domain\Enum\EvaluationType;
use Shared\Domain\Enum\RecurrenceType;
use Shared\Domain\Enum\SalesMetricType;
use Tests\Company\Application\Controllers\CompanyControllerTestCase;
use Tests\resources\Application\EntityRecord;

class StrikerMetricControllerTest extends CompanyControllerTestCase
{
    protected EntityRecord $strikerMetricOne, $strikerMetricTwo;
    protected $strikerMetricPayload;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection->table('StrikerMetric')->truncate();
        //
        $this->strikerMetricOne = new EntityRecord(StrikerMetric::class, 'One');
        $this->strikerMetricTwo = new EntityRecord(StrikerMetric::class, 'Two');
        //
        $this->strikerMetricPayload = [
            'name' => 'new striker metric',
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
        $this->connection->table('StrikerMetric')->truncate();
    }
    
    //
    protected function createStrikerMetric()
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
    createStrikerMetric (
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
        $this->graphqlVariables = $this->strikerMetricPayload;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_createStrikerMetric_200()
    {
$this->disableExceptionHandling();
        $this->createStrikerMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'disabled' => false,
            'name' => $this->strikerMetricPayload['name'],
            'target' => $this->strikerMetricPayload['target'],
            'dailyReminderTarget' => $this->strikerMetricPayload['dailyReminderTarget'],
            'evaluationType' => $this->strikerMetricPayload['evaluationType'],
            'recurrenceType' => $this->strikerMetricPayload['recurrenceType'],
            'salesMetricType' => $this->strikerMetricPayload['salesMetricType'],
            'recurrenceCount' => $this->strikerMetricPayload['recurrenceCount'],
        ]);
        
        $this->seeInDatabase('StrikerMetric', [
            'disabled' => false,
            'createdTime' => $this->stringOfCurrentTime(),
            'name' => $this->strikerMetricPayload['name'],
            'target' => $this->strikerMetricPayload['target'],
            'dailyReminderTarget' => $this->strikerMetricPayload['dailyReminderTarget'],
            'evaluationType' => $this->strikerMetricPayload['evaluationType'],
            'recurrenceType' => $this->strikerMetricPayload['recurrenceType'],
            'salesMetricType' => $this->strikerMetricPayload['salesMetricType'],
            'recurrenceCount' => $this->strikerMetricPayload['recurrenceCount'],
        ]);
    }
    
    //
    protected function updateStrikerMetric()
    {
        $this->prepareAdminDependency();
        $this->strikerMetricOne->insert($this->connection);
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
    updateStrikerMetric (
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
            'id' => $this->strikerMetricOne->columns['id'],
            ...$this->strikerMetricPayload,
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_updateStrikerMetric_200()
    {
$this->disableExceptionHandling();
        $this->updateStrikerMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'target' => $this->strikerMetricPayload['target'],
            'dailyReminderTarget' => $this->strikerMetricPayload['dailyReminderTarget'],
            'evaluationType' => $this->strikerMetricPayload['evaluationType'],
            'recurrenceType' => $this->strikerMetricPayload['recurrenceType'],
            'salesMetricType' => $this->strikerMetricPayload['salesMetricType'],
            'recurrenceCount' => $this->strikerMetricPayload['recurrenceCount'],
        ]);
        
        $this->seeInDatabase('StrikerMetric', [
            'id' => $this->strikerMetricOne->columns['id'],
            'target' => $this->strikerMetricPayload['target'],
            'dailyReminderTarget' => $this->strikerMetricPayload['dailyReminderTarget'],
            'evaluationType' => $this->strikerMetricPayload['evaluationType'],
            'recurrenceType' => $this->strikerMetricPayload['recurrenceType'],
            'salesMetricType' => $this->strikerMetricPayload['salesMetricType'],
            'recurrenceCount' => $this->strikerMetricPayload['recurrenceCount'],
        ]);
    }
    
    //
    protected function disableStrikerMetric()
    {
        $this->prepareAdminDependency();
        $this->strikerMetricOne->insert($this->connection);
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $id: ID,
) {
    disableStrikerMetric (
        id: $id,
    ) {
        disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->strikerMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_disableStrikerMetric_200()
    {
$this->disableExceptionHandling();
        $this->disableStrikerMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'disabled' => true,
        ]);
        
        $this->seeInDatabase('StrikerMetric', [
            'id' => $this->strikerMetricOne->columns['id'],
            'disabled' => true,
        ]);
    }
    
    //
    protected function enableStrikerMetric()
    {
        $this->prepareAdminDependency();
        $this->strikerMetricOne->columns['disabled'] = true;
        $this->strikerMetricOne->insert($this->connection);
        $this->graphqlQuery = <<<'_QUERY'
mutation (
    $id: ID,
) {
    enableStrikerMetric (
        id: $id,
    ) {
        disabled
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->strikerMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_enableStrikerMetric_200()
    {
$this->disableExceptionHandling();
        $this->enableStrikerMetric();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'disabled' => false,
        ]);
        
        $this->seeInDatabase('StrikerMetric', [
            'id' => $this->strikerMetricOne->columns['id'],
            'disabled' => false,
        ]);
    }
    
    //
    protected function viewStrikerMetricDetail()
    {
        $this->prepareAdminDependency();
        $this->strikerMetricOne->insert($this->connection);
        $this->graphqlQuery = <<<'_QUERY'
query (
    $id: ID,
) {
    viewStrikerMetricDetail (
        id: $id,
    ) {
        target, dailyReminderTarget, evaluationType, recurrenceType, salesMetricType, recurrenceCount,
    }
}
_QUERY;
        $this->graphqlVariables = [
            'id' => $this->strikerMetricOne->columns['id'],
        ];
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewStrikerMetricDetail_200()
    {
$this->disableExceptionHandling();
        $this->viewStrikerMetricDetail();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'target' => $this->strikerMetricOne->columns['target'],
            'dailyReminderTarget' => $this->strikerMetricOne->columns['dailyReminderTarget'],
            'evaluationType' => $this->strikerMetricOne->columns['evaluationType'],
            'recurrenceType' => $this->strikerMetricOne->columns['recurrenceType'],
            'salesMetricType' => $this->strikerMetricOne->columns['salesMetricType'],
            'recurrenceCount' => $this->strikerMetricOne->columns['recurrenceCount'],
        ]);
    }
    
    //
    protected function viewStrikerMetricList()
    {
        $this->prepareAdminDependency();
        $this->strikerMetricOne->insert($this->connection);
        $this->strikerMetricTwo->insert($this->connection);
        $this->graphqlQuery = <<<'_QUERY'
query {
    viewStrikerMetricList {
        list {
            id, disabled, target, dailyReminderTarget, evaluationType, recurrenceType, salesMetricType, recurrenceCount 
        },
        cursorLimit { total }
    }
}
_QUERY;
        $this->postGraphqlRequest($this->admin->token);
    }
    public function test_viewStrikerMetricList_200()
    {
$this->disableExceptionHandling();
        $this->viewStrikerMetricList();
        $this->seeStatusCode(200);
        
        $this->seeJsonContains([
            'list' => [
                [
                    'id' => $this->strikerMetricOne->columns['id'],
                    'disabled' => $this->strikerMetricOne->columns['disabled'],
                    'target' => $this->strikerMetricOne->columns['target'],
                    'dailyReminderTarget' => $this->strikerMetricOne->columns['dailyReminderTarget'],
                    'evaluationType' => $this->strikerMetricOne->columns['evaluationType'],
                    'recurrenceType' => $this->strikerMetricOne->columns['recurrenceType'],
                    'salesMetricType' => $this->strikerMetricOne->columns['salesMetricType'],
                    'recurrenceCount' => $this->strikerMetricOne->columns['recurrenceCount'],
                ],
                [
                    'id' => $this->strikerMetricTwo->columns['id'],
                    'disabled' => $this->strikerMetricTwo->columns['disabled'],
                    'target' => $this->strikerMetricTwo->columns['target'],
                    'dailyReminderTarget' => $this->strikerMetricTwo->columns['dailyReminderTarget'],
                    'evaluationType' => $this->strikerMetricTwo->columns['evaluationType'],
                    'recurrenceType' => $this->strikerMetricTwo->columns['recurrenceType'],
                    'salesMetricType' => $this->strikerMetricTwo->columns['salesMetricType'],
                    'recurrenceCount' => $this->strikerMetricTwo->columns['recurrenceCount'],
                ],
            ],
            'cursorLimit' => [
                'total' => 2,
            ],
        ]);
    }
}
