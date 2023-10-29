<?php

namespace App\Http\Controllers\SalesBC;

use App\Http\Controllers\Controller;
use App\Http\Controllers\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;
use Sales\Domain\Model\SalesActivity;
use Sales\Domain\Task\SalesActivitySchedule\SubmitScheduleTask;
use Sales\Domain\Task\SalesActivitySchedule\ViewSalesActivityScheduleDetailTask;
use Sales\Domain\Task\SalesActivitySchedule\ViewSalesActivityScheduleListTask;
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
        $task = new SubmitScheduleTask($repository, $assignedCustomerRepository, $salesActivityRepository);

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
    
    public function viewDetail(SalesRoleInterface $user, string $id)
    {
        $task = new ViewSalesActivityScheduleDetailTask($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeSalesTask($task, $payload);
        
        return $payload->result;
    }
}
