<?php

namespace App\Http\Controllers\SalesBC\BySales;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SalesBC\SalesRole;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReportData;
use Sales\Domain\Task\BySales\SalesActivityReport\SubmitInitialSalesActivityReport;
use Sales\Domain\Task\BySales\SalesActivityReport\SubmitInitialSalesActivityReportPayload;
use Sales\Domain\Task\BySales\SalesActivityReport\SubmitSalesActivityReportTask;
use Sales\Domain\Task\BySales\SalesActivityReport\ViewSalesActivityReportDetailTask;
use Sales\Domain\Task\BySales\SalesActivityReport\ViewSalesActivityReportListTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityReportRepository;

#[GraphqlMapableController(entity: SalesActivityReport::class)]
class SalesActivityReportController extends Controller
{

    protected function repository(): DoctrineSalesActivityReportRepository
    {
        return $this->em->getRepository(SalesActivityReport::class);
    }

    //
    public function submitInitialSalesActivityReport(SalesRole $user, InputRequest $input)
    {
        $repository = $this->repository();
        $customerAssignmentRepository = $this->em->getRepository(CustomerAssignment::class);
        $salesActivityScheduleRepository = $this->em->getRepository(SalesActivitySchedule::class);
        $salesActivityRepository = $this->em->getRepository(SalesActivity::class);
        $task = new SubmitInitialSalesActivityReport(
                $repository, $customerAssignmentRepository, $salesActivityScheduleRepository, $salesActivityRepository);

        $payload = (new SubmitInitialSalesActivityReportPayload($input->get('content')))
                ->setCustomerAssignmentId($input->get('CustomerAssignment_id'));

        $user->executeSalesTask($task, $payload);
        return $salesActivityScheduleRepository->queryOneById($payload->salesActivityScheduleId);
    }
    
    #[Mutation]
    public function submitSalesActivityReport(SalesRole $user, string $SalesActivitySchedule_id, InputRequest $input)
    {
        $repository = $this->repository();
        $salesActivityScheduleRepository = $this->em->getRepository(SalesActivitySchedule::class);
        $task = new SubmitSalesActivityReportTask($repository, $salesActivityScheduleRepository);

        $payload = (new SalesActivityReportData($input->get('content')))
                ->setSalesActivityScheduleId($SalesActivitySchedule_id);

        $user->executeSalesTask($task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function salesActivityReportList(SalesRole $user, InputRequest $input)
    {
        $task = new ViewSalesActivityReportListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeSalesTask($task, $payload);

        return $payload->result;
    }

    #[Query]
    public function salesActivityReportDetail(SalesRole $user, string $id)
    {
        $task = new ViewSalesActivityReportDetailTask($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeSalesTask($task, $payload);

        return $payload->result;
    }
}
