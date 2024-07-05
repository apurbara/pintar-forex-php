<?php

namespace Sales\Application\Controllers;

use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Domain\TaskPayload\ViewPayload;
use Resources\Domain\TaskPayload\ViewSummaryPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Domain\Task\SalesActivitySchedule\SubmitScheduleTask;
use Sales\Domain\Task\SalesActivitySchedule\ViewAllNonInitialSchedules;
use Sales\Domain\Task\SalesActivitySchedule\ViewAllNonInitialSchedulesInMonth;
use Sales\Domain\Task\SalesActivitySchedule\ViewAllNonInitialSchedulesInMonthPayload;
use Sales\Domain\Task\SalesActivitySchedule\ViewSalesActivityScheduleDetailTask;
use Sales\Domain\Task\SalesActivitySchedule\ViewSalesActivityScheduleListTask;
use Sales\Domain\Task\SalesActivitySchedule\ViewSalesActivityScheduleSummary;
use Sales\Domain\Task\SalesActivitySchedule\ViewTotalSalesActivitySchedule;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityScheduleRepository;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;

#[GraphqlMapableController(entity: SalesActivitySchedule::class)]
class SalesActivityScheduleController extends BaseController
{

    protected function repository(): DoctrineSalesActivityScheduleRepository
    {
        return $this->em->getRepository(SalesActivitySchedule::class);
    }

    //
    #[Mutation]
    public function submitSalesActivitySchedule(Sales $sales, string $CustomerAssignment_id, InputRequest $input)
    {
        $repository = $this->repository();
        $customerAssignmentRepository = $this->em->getRepository(CustomerAssignment::class);
        $salesActivityRepository = $this->em->getRepository(SalesActivity::class);
        $schedulerService = new SalesActivitySchedulerService();
//        $task = new SubmitScheduleTask($repository, $customerAssignmentRepository, $salesActivityRepository);
        $task = new SubmitScheduleTask($repository, $customerAssignmentRepository, $salesActivityRepository, $schedulerService);

        $hourlyTimeIntervalData = new HourlyTimeIntervalData($input->get('startTime'));
        $payload = (new SalesActivityScheduleData($hourlyTimeIntervalData))
                ->setCustomerAssignmentId($CustomerAssignment_id)
                ->setSalesActivityId($input->get('SalesActivity_id'));
        
        $this->executeSalesMutationTask($sales, $task, $payload);
        return $repository->queryOneById($payload->id);
    }
    
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function salesActivityScheduleList(Sales $sales, InputRequest $input)
    {
        $task = new ViewSalesActivityScheduleListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        
        $sales->executeTask($task, $payload);
        return $payload->result;
    }
    
    public function salesActivityScheduleSummaryList(Sales $sales, InputRequest $input)
    {
        $task = new ViewSalesActivityScheduleSummary($this->repository());
        $payload = $this->buildViewAllListPayload($input);
        
        $sales->executeTask($task, $payload);
        return $payload->result;
    }
    
    #[Query]
    public function salesActivityScheduleDetail(Sales $sales, string $id)
    {
        $task = new ViewSalesActivityScheduleDetailTask($this->repository());
        $payload = new ViewDetailPayload($id);
        
        $sales->executeTask($task, $payload);
        return $payload->result;
    }
    
    public function totalSalesActivitySchedule(Sales $sales, InputRequest $input)
    {
        $task = new ViewTotalSalesActivitySchedule($this->repository());
        $searchSchema = [
            'filters' => $input->get('filters'),
        ];
        $payload = new ViewSummaryPayload($searchSchema);
        
        $sales->executeTask($task, $payload);
        return $payload->result;
    }
    
    public function viewAllNonInitialSchedulesInMonth(Sales $sales, InputRequest $input)
    {
        $task = new ViewAllNonInitialSchedulesInMonth($this->repository());
        $payload = (new ViewAllNonInitialSchedulesInMonthPayload())
                ->setYear($input->get('year'))
                ->setMonth($input->get('month'));
        
        $sales->executeTask($task, $payload);
        return $payload->result;
    }
    
    #[Query(responseWrapper:Query::LIST_RESPONSE_WRAPPER)]
    public function viewAllNonInitialSchedules(Sales $sales)
    {
        $task = new ViewAllNonInitialSchedules($this->repository());
        $payload = new ViewPayload();
        
        $sales->executeTask($task, $payload);
        return $payload->result;
    }
}
