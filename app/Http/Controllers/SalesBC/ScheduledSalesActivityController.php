<?php

namespace App\Http\Controllers\SalesBC;

use App\Http\Controllers\Controller;
use App\Http\Controllers\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ScheduledSalesActivity;
use Sales\Domain\Model\SalesActivity;
use Sales\Domain\Task\ScheduledSalesActivity\SubmitScheduleTask;
use Sales\Domain\Task\ScheduledSalesActivity\ViewScheduledSalesActivityDetailTask;
use Sales\Domain\Task\ScheduledSalesActivity\ViewScheduledSalesActivityListTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineScheduledSalesActivityRepository;
use SharedContext\Domain\ValueObject\HourlyTimeIntervalData;

class ScheduledSalesActivityController extends Controller
{

    protected function repository(): DoctrineScheduledSalesActivityRepository
    {
        return $this->em->getRepository(ScheduledSalesActivity::class);
    }

    //
    public function submitSchedule(SalesRoleInterface $user, string $assignedCustomerId, InputRequest $input)
    {
        $repository = $this->repository();
        $assignedCustomerRepository = $this->em->getRepository(AssignedCustomer::class);
        $salesActivityRepository = $this->em->getRepository(SalesActivity::class);
        $task = new SubmitScheduleTask($repository, $assignedCustomerRepository, $salesActivityRepository);

        $hourlyTimeIntervalData = new HourlyTimeIntervalData($input->get('startTime'));
        $payload = (new AssignedCustomer\ScheduledSalesActivityData($hourlyTimeIntervalData))
                ->setAssignedCustomerId($assignedCustomerId)
                ->setSalesActivityId($input->get('salesActivityId'));
        
        $user->executeTask($task, $payload);
        return $repository->fetchOneByIdOrDie($payload->id);
    }
    
    public function viewList(SalesRoleInterface $user, InputRequest $input)
    {
        $task = new ViewScheduledSalesActivityListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeTask($task, $payload);
        
        return $payload->result;
    }
    
    public function viewDetail(SalesRoleInterface $user, string $id)
    {
        $task = new ViewScheduledSalesActivityDetailTask($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeTask($task, $payload);
        
        return $payload->result;
    }
}
