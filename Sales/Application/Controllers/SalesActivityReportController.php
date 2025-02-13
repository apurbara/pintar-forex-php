<?php

namespace Sales\Application\Controllers;

use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReportData;
use Sales\Domain\Task\SalesActivityReport\SubmitInitialSalesActivityReport;
use Sales\Domain\Task\SalesActivityReport\SubmitInitialSalesActivityReportPayload;
use Sales\Domain\Task\SalesActivityReport\SubmitNonScheduledSalesActivityReport;
use Sales\Domain\Task\SalesActivityReport\SubmitNonScheduledSalesActivityReportPayload;
use Sales\Domain\Task\SalesActivityReport\SubmitSalesActivityReportTask;
use Sales\Domain\Task\SalesActivityReport\ViewSalesActivityReportDetailTask;
use Sales\Domain\Task\SalesActivityReport\ViewSalesActivityReportListTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityReportRepository;

#[GraphqlMapableController(entity: SalesActivityReport::class)]
class SalesActivityReportController extends BaseController
{

    protected function repository(): DoctrineSalesActivityReportRepository
    {
        return $this->em->getRepository(SalesActivityReport::class);
    }

    //
    public function submitInitialSalesActivityReport(Sales $sales, InputRequest $input)
    {
        $repository = $this->repository();
        $customerAssignmentRepository = $this->em->getRepository(CustomerAssignment::class);
        $salesActivityScheduleRepository = $this->em->getRepository(SalesActivitySchedule::class);
        $salesActivityRepository = $this->em->getRepository(SalesActivity::class);
        $task = new SubmitInitialSalesActivityReport(
                $repository, $customerAssignmentRepository, $salesActivityScheduleRepository, $salesActivityRepository);

        $payload = (new SubmitInitialSalesActivityReportPayload($input->get('content')))
                ->setCustomerAssignmentId($input->get('CustomerAssignment_id'));

        $this->executeSalesMutationTask($sales, $task, $payload);
        return $salesActivityScheduleRepository->queryOneById($payload->salesActivityScheduleId);
    }
    public function submitNonScheduledSalesActivityReport(Sales $sales, InputRequest $input)
    {
        $repository = $this->repository();
        $customerAssignmentRepository = $this->em->getRepository(CustomerAssignment::class);
        $salesActivityScheduleRepository = $this->em->getRepository(SalesActivitySchedule::class);
        $salesActivityRepository = $this->em->getRepository(SalesActivity::class);
        $task = new SubmitNonScheduledSalesActivityReport(
                $repository, $customerAssignmentRepository, $salesActivityScheduleRepository, $salesActivityRepository);

        $payload = (new SubmitNonScheduledSalesActivityReportPayload($input->get('content')))
                ->setCustomerAssignmentId($input->get('CustomerAssignment_id'))
                ->setSalesActivityId($input->get('SalesActivity_id') ?? null);

        $this->executeSalesMutationTask($sales, $task, $payload);
        return $salesActivityScheduleRepository->queryOneById($payload->salesActivityScheduleId);
    }
    
    #[Mutation]
    public function submitSalesActivityReport(Sales $sales, string $SalesActivitySchedule_id, InputRequest $input)
    {
        $repository = $this->repository();
        $salesActivityScheduleRepository = $this->em->getRepository(SalesActivitySchedule::class);
        $task = new SubmitSalesActivityReportTask($repository, $salesActivityScheduleRepository);

        $payload = (new SalesActivityReportData($input->get('content')))
                ->setSalesActivityScheduleId($SalesActivitySchedule_id);

        $this->executeSalesMutationTask($sales, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function salesActivityReportList(Sales $sales, InputRequest $input)
    {
        $task = new ViewSalesActivityReportListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }

    #[Query]
    public function salesActivityReportDetail(Sales $sales, string $id)
    {
        $task = new ViewSalesActivityReportDetailTask($this->repository());
        $payload = new ViewDetailPayload($id);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }
}
