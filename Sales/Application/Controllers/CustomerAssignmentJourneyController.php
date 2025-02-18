<?php

namespace Sales\Application\Controllers;

use Resources\Application\InputRequest;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment\CustomerAssignmentJourney;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerAssignmentJourneyRepository;

#[GraphqlMapableController(entity: CustomerAssignmentJourney::class)]
class CustomerAssignmentJourneyController extends BaseController
{

    protected function repository(): DoctrineCustomerAssignmentJourneyRepository
    {
        return $this->em->getRepository(CustomerAssignmentJourney::class);
    }

    //
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewCustomerAssignmentJourneyList(Sales $sales, InputRequest $input)
    {
        $task = new \Sales\Domain\Task\CustomerAssignmentJourney\ViewCustomerAssignmentJourneyList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }
}
