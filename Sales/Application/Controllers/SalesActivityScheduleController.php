<?php

namespace Sales\Application\Controllers;

use GraphQL\Type\Definition\IntType;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Domain\TaskPayload\ViewSummaryPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Application\GraphQL\Object\SalesActivityScheduleGraphqlObjectInSalesBC;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivityScheduleData;
use Sales\Domain\Model\Sales\FactFindingAssignment;
use Sales\Domain\Model\Sales\GreetingAssignment;
use Sales\Domain\Model\Sales\StrikingAssignment;
use Sales\Domain\Task\SalesActivitySchedule\SubmitScheduleTask;
use Sales\Domain\Task\SalesActivitySchedule\ViewAllOngoingSchedule;
use Sales\Domain\Task\SalesActivitySchedule\ViewSalesActivityScheduleDetail;
use Sales\Domain\Task\SalesActivitySchedule\ViewSalesActivityScheduleList;
use Sales\Domain\Task\SalesActivitySchedule\ViewTotalSalesActivitySchedule;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityScheduleRepository;
use Shared\Domain\Enum\SalesRole;
use Shared\Domain\ValueObject\HourlyTimeIntervalData;

#[GraphqlMapableController(entity: SalesActivitySchedule::class,
            responseType: SalesActivityScheduleGraphqlObjectInSalesBC::class)]
class SalesActivityScheduleController extends BaseController
{

    protected function repository(): DoctrineSalesActivityScheduleRepository
    {
        return $this->em->getRepository(SalesActivitySchedule::class);
    }

    //
    #[Mutation]
    public function submitSalesActivitySchedule(
            Sales $sales, string $CustomerAssignment_id, InputRequest $input)
    {
        $repository = $this->repository();
        $customerAssignmentRepository = match ($sales->getRole()) {
            SalesRole::GREETER => $this->em->getRepository(GreetingAssignment::class),
            SalesRole::FACT_FINDER => $this->em->getRepository(FactFindingAssignment::class),
            SalesRole::STRIKER => $this->em->getRepository(StrikingAssignment::class),
        };
        $salesActivityRepository = $this->em->getRepository(SalesActivity::class);
        $task = new SubmitScheduleTask($repository, $customerAssignmentRepository, $salesActivityRepository);

        $hourlyTimeIntervalData = new HourlyTimeIntervalData($input->get('startTime'));
        $payload = (new SalesActivityScheduleData($hourlyTimeIntervalData))
                ->setCustomerAssignmentId($CustomerAssignment_id)
                ->setSalesActivityId($input->get('SalesActivity_id'));

        $sales->executeTask($task, $payload);
        $this->em->flush();

        return $repository->queryOneById($payload->id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function salesActivityScheduleList(Sales $sales, InputRequest $input)
    {
        $task = new ViewSalesActivityScheduleList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }

    #[Query]
    public function salesActivityScheduleDetail(Sales $sales, string $id)
    {
        $task = new ViewSalesActivityScheduleDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::SUMMARY_RESPONSE_WRAPPER, responseType: IntType::class)]
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

    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER)]
    public function viewAllOngoingSchedule(Sales $sales, InputRequest $input)
    {
        $task = new ViewAllOngoingSchedule($this->repository());
        $payload = $this->buildViewAllListPayload($input);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }
}
