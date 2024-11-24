<?php

namespace Sales\Application\Controllers;

use Resources\Application\InputRequest;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule\SalesActivityReportData;
use Sales\Domain\Model\Sales\FactFindingAssignment;
use Sales\Domain\Model\Sales\GreetingAssignment;
use Sales\Domain\Model\Sales\StrikingAssignment;
use Sales\Domain\Task\Dependency\ContainCustomerAssignmentRepository;
use Sales\Domain\Task\SalesActivityReport\SubmitNonScheduleSalesActivityReport;
use Sales\Domain\Task\SalesActivityReport\SubmitNonScheduleSalesActivityReportPayload;
use Sales\Domain\Task\SalesActivityReport\SubmitSalesActivityReportTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityReportRepository;

#[GraphqlMapableController(entity: SalesActivityReport::class)]
class SalesActivityReportController extends BaseController
{

    protected function repository(): DoctrineSalesActivityReportRepository
    {
        return $this->em->getRepository(SalesActivityReport::class);
    }

    //
    private function submitNonScheduledSalesActivityReport(
            Sales $sales, string $customerAssignmentId, InputRequest $input,
            ContainCustomerAssignmentRepository $customerAssignmentRepository)
    {
        $repository = $this->repository();
        $salesActivityRepository = $this->em->getRepository(SalesActivity::class);
        $task = new SubmitNonScheduleSalesActivityReport(
                $repository, $customerAssignmentRepository, $salesActivityRepository);
        $payload = (new SubmitNonScheduleSalesActivityReportPayload($input->get('content')))
                ->setCustomerAssignmentId($customerAssignmentId)
                ->setSalesActivityId($input->get('SalesActivity_id'));

        $this->executeSalesMutationTask($sales, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    public function submitNonScheduleGreetingActivityReport(Sales $sales, string $CustomerAssignment_id,
            InputRequest $input)
    {
        $greetingAssignmentRepository = $this->em->getRepository(GreetingAssignment::class);
        return $this->submitNonScheduledSalesActivityReport(
                        $sales, $CustomerAssignment_id, $input, $greetingAssignmentRepository);
    }

    public function submitNonScheduleFactFindingActivityReport(Sales $sales, string $CustomerAssignment_id,
            InputRequest $input)
    {
        $factFindingAssignmentRepository = $this->em->getRepository(FactFindingAssignment::class);
        return $this->submitNonScheduledSalesActivityReport(
                        $sales, $CustomerAssignment_id, $input, $factFindingAssignmentRepository);
    }

    public function submitNonScheduleStrikingActivityReport(Sales $sales, string $CustomerAssignment_id,
            InputRequest $input)
    {
        $strikingAssignmentRepository = $this->em->getRepository(StrikingAssignment::class);
        return $this->submitNonScheduledSalesActivityReport(
                        $sales, $CustomerAssignment_id, $input, $strikingAssignmentRepository);
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
}
