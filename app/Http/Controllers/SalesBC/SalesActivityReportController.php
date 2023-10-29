<?php

namespace App\Http\Controllers\SalesBC;

use App\Http\Controllers\Controller;
use App\Http\Controllers\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReportData;
use Sales\Domain\Task\SalesActivityReport\SubmitSalesActivityReportTask;
use Sales\Domain\Task\SalesActivityReport\ViewSalesActivityReportDetailTask;
use Sales\Domain\Task\SalesActivityReport\ViewSalesActivityReportListTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityReportRepository;

class SalesActivityReportController extends Controller
{

    protected function repository(): DoctrineSalesActivityReportRepository
    {
        return $this->em->getRepository(SalesActivityReport::class);
    }

    //
    public function submitReport(SalesRoleInterface $user, string $salesActivityScheduleId,            InputRequest $input)
    {
        $repository = $this->repository();
        $salesActivityScheduleRepository = $this->em->getRepository(SalesActivitySchedule::class);
        $task = new SubmitSalesActivityReportTask($repository, $salesActivityScheduleRepository);

        $payload = (new SalesActivityReportData($input->get('content')))
                ->setSalesActivityScheduleId($salesActivityScheduleId);
        
        $user->executeSalesTask($task, $payload);
        return $repository->fetchOneByIdOrDie($payload->id);
    }
    
    public function viewList(SalesRoleInterface $user, InputRequest $input)
    {
        $task = new ViewSalesActivityReportListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeSalesTask($task, $payload);
        
        return $payload->result;
    }
    
    public function viewDetail(SalesRoleInterface $user, string $id)
    {
        $task = new ViewSalesActivityReportDetailTask($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeSalesTask($task, $payload);
        
        return $payload->result;
    }
}
