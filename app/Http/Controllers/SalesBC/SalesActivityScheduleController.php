<?php

namespace App\Http\Controllers\SalesBC;

use App\Http\Controllers\Controller;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Domain\TaskPayload\ViewSummaryPayload;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;
use Sales\Domain\Model\SalesActivity;
use Sales\Domain\Service\SalesActivitySchedulerService;
use Sales\Domain\Task\SalesActivitySchedule\SubmitScheduleTask;
use Sales\Domain\Task\SalesActivitySchedule\ViewSalesActivityScheduleDetailTask;
use Sales\Domain\Task\SalesActivitySchedule\ViewSalesActivityScheduleListTask;
use Sales\Domain\Task\SalesActivitySchedule\ViewSalesActivityScheduleSummary;
use Sales\Domain\Task\SalesActivitySchedule\ViewTotalSalesActivitySchedule;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityScheduleRepository;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;

class SalesActivityScheduleController extends Controller
{

    protected function repository(): DoctrineSalesActivityScheduleRepository
    {
        return $this->em->getRepository(SalesActivitySchedule::class);
    }

    //
    public function submitSchedule(SalesRoleInterface $user, string $assignedCustomerId, InputRequest $input)
    {
        $repository = $this->repository();
        $assignedCustomerRepository = $this->em->getRepository(AssignedCustomer::class);
        $salesActivityRepository = $this->em->getRepository(SalesActivity::class);
        $schedulerService = new SalesActivitySchedulerService();
//        $task = new SubmitScheduleTask($repository, $assignedCustomerRepository, $salesActivityRepository);
        $task = new SubmitScheduleTask($repository, $assignedCustomerRepository, $salesActivityRepository, $schedulerService);

        $hourlyTimeIntervalData = new HourlyTimeIntervalData($input->get('startTime'));
        $payload = (new AssignedCustomer\SalesActivityScheduleData($hourlyTimeIntervalData))
                ->setAssignedCustomerId($assignedCustomerId)
                ->setSalesActivityId($input->get('salesActivityId'));
        
        $user->executeSalesTask($task, $payload);
        return $repository->fetchOneByIdOrDie($payload->id);
    }
    
    public function viewList(SalesRoleInterface $user, InputRequest $input)
    {
        $task = new ViewSalesActivityScheduleListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeSalesTask($task, $payload);
        
        return $payload->result;
    }
    
    public function viewSummaryList(SalesRoleInterface $user, InputRequest $input)
    {
        $task = new ViewSalesActivityScheduleSummary($this->repository());
        $payload = $this->buildViewAllListPayload($input);
        $user->executeSalesTask($task, $payload);
        
        return $payload->result;
    }
    
    public function viewDetail(SalesRoleInterface $user, string $id)
    {
        $task = new ViewSalesActivityScheduleDetailTask($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeSalesTask($task, $payload);
        
        return $payload->result;
    }
    
    public function viewTotalSchedule(SalesRoleInterface $user, InputRequest $input)
    {
        $task = new ViewTotalSalesActivitySchedule($this->repository());
        $searchSchema = [
            'filters' => $input->get('filters'),
        ];
        $payload = new ViewSummaryPayload($searchSchema);
        $user->executeSalesTask($task, $payload);
        
        return $payload->result;
    }
}
