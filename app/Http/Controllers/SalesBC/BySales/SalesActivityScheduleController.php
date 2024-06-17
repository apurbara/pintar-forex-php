<?php

namespace App\Http\Controllers\SalesBC\BySales;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SalesBC\SalesRole;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Domain\TaskPayload\ViewSummaryPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Domain\Task\BySales\SalesActivitySchedule\SubmitScheduleTask;
use Sales\Domain\Task\BySales\SalesActivitySchedule\ViewAllNonInitialSchedulesInMonth;
use Sales\Domain\Task\BySales\SalesActivitySchedule\ViewAllNonInitialSchedulesInMonthPayload;
use Sales\Domain\Task\BySales\SalesActivitySchedule\ViewSalesActivityScheduleDetailTask;
use Sales\Domain\Task\BySales\SalesActivitySchedule\ViewSalesActivityScheduleListTask;
use Sales\Domain\Task\BySales\SalesActivitySchedule\ViewSalesActivityScheduleSummary;
use Sales\Domain\Task\BySales\SalesActivitySchedule\ViewTotalSalesActivitySchedule;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityScheduleRepository;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;

#[GraphqlMapableController(entity: SalesActivitySchedule::class)]
class SalesActivityScheduleController extends Controller
{

    protected function repository(): DoctrineSalesActivityScheduleRepository
    {
        return $this->em->getRepository(SalesActivitySchedule::class);
    }

    //
    #[Mutation]
    public function submitSalesActivitySchedule(SalesRole $user, string $CustomerAssignment_id, InputRequest $input)
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
        
        $user->executeSalesTask($task, $payload);
        return $repository->queryOneById($payload->id);
    }
    
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function salesActivityScheduleList(SalesRole $user, InputRequest $input)
    {
        $task = new ViewSalesActivityScheduleListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeSalesTask($task, $payload);
        
        return $payload->result;
    }
    
    public function salesActivityScheduleSummaryList(SalesRole $user, InputRequest $input)
    {
        $task = new ViewSalesActivityScheduleSummary($this->repository());
        $payload = $this->buildViewAllListPayload($input);
        $user->executeSalesTask($task, $payload);
        
        return $payload->result;
    }
    
    #[Query]
    public function salesActivityScheduleDetail(SalesRole $user, string $id)
    {
        $task = new ViewSalesActivityScheduleDetailTask($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeSalesTask($task, $payload);
        
        return $payload->result;
    }
    
    public function totalSalesActivitySchedule(SalesRole $user, InputRequest $input)
    {
        $task = new ViewTotalSalesActivitySchedule($this->repository());
        $searchSchema = [
            'filters' => $input->get('filters'),
        ];
        $payload = new ViewSummaryPayload($searchSchema);
        $user->executeSalesTask($task, $payload);
        
        return $payload->result;
    }
    
    public function viewAllNonInitialSchedulesInMonth(SalesRole $user, InputRequest $input)
    {
        $task = new ViewAllNonInitialSchedulesInMonth($this->repository());
        $payload = (new ViewAllNonInitialSchedulesInMonthPayload())
                ->setYear($input->get('year'))
                ->setMonth($input->get('month'));
        
        $user->executeSalesTask($task, $payload);
        return $payload->result;
    }
}
