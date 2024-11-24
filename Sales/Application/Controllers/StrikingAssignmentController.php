<?php

namespace Sales\Application\Controllers;

use GraphQL\Type\Definition\IntType;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Domain\DependencyModel\CustomerJourney;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\StrikingAssignment;
use Sales\Domain\Task\StrikingAssignment\UpdateJourney;
use Sales\Domain\Task\StrikingAssignment\UpdateJourneyPayload;
use Sales\Domain\Task\StrikingAssignment\ViewStrikingAssignmentDetail;
use Sales\Domain\Task\StrikingAssignment\ViewStrikingAssignmentList;
use Sales\Domain\Task\StrikingAssignment\ViewTotalStrikingAssignment;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineStrikingAssignmentRepository;
use Shared\Application\Controllers\Controller;

#[GraphqlMapableController(entity: StrikingAssignment::class)]
class StrikingAssignmentController extends Controller
{

    private function repository(): DoctrineStrikingAssignmentRepository
    {
        return $this->em->getRepository(StrikingAssignment::class);
    }

    //
    #[Mutation]
    public function updateJourney(Sales $sales, string $id, InputRequest $inputRequest)
    {
        $repository = $this->repository();
        $customerJourneyRepository = $this->em->getRepository(CustomerJourney::class);
        $task = new UpdateJourney($repository, $customerJourneyRepository);
        $payload = (new UpdateJourneyPayload())
                ->setCustomerJourneyId($inputRequest->get('CustomerJourney_id'))
                ->setId($id);
        
        $sales->executeTask($task, $payload);
        $this->em->flush();
        return $repository->queryOneById($payload->id);
    }

    //
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function strikingAssignmentList(Sales $sales, InputRequest $input)
    {
        $task = new ViewStrikingAssignmentList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }

    #[Query]
    public function strikingAssignmentDetail(Sales $sales, string $id)
    {

        $task = new ViewStrikingAssignmentDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::SUMMARY_RESPONSE_WRAPPER, responseType: IntType::class)]
    public function totalStrikingAssignment(Sales $sales, InputRequest $input)
    {
        $task = new ViewTotalStrikingAssignment($this->repository());
        $payload = $this->buildViewSummaryPayload($input);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }
}
