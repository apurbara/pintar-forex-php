<?php

namespace Sales\Application\Controllers;

use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignment\RecycleRequest;
use Sales\Domain\Model\Sales\CustomerAssignment\RecycleRequestData;
use Sales\Domain\Task\RecycleRequest\SubmitRecycleRequestTask;
use Sales\Domain\Task\RecycleRequest\UpdateRecycleRequestTask;
use Sales\Domain\Task\RecycleRequest\ViewRecycleRequestDetail;
use Sales\Domain\Task\RecycleRequest\ViewRecycleRequestListTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineRecycleRequestRepository;

#[GraphqlMapableController(entity: RecycleRequest::class)]
class RecycleRequestController extends BaseController
{

    protected function repository(): DoctrineRecycleRequestRepository
    {
        return $this->em->getRepository(RecycleRequest::class);
    }

    //
    #[Mutation]
    public function submitRecycleRequest(Sales $sales, string $CustomerAssignment_id, InputRequest $input)
    {
        $repository = $this->repository();
        $customerAssignmentRepository = $this->em->getRepository(CustomerAssignment::class);
        $task = new SubmitRecycleRequestTask($repository, $customerAssignmentRepository);

        $note = $input->get('note');
        $payload = (new RecycleRequestData($note))
                ->setCustomerAssignmentId($CustomerAssignment_id);

        $this->executeSalesMutationTask($sales, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateRecycleRequest(Sales $sales, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateRecycleRequestTask($repository);

        $note = $input->get('note');
        $payload = (new RecycleRequestData($note))->setId($id);

        $this->executeSalesMutationTask($sales, $task, $payload);
        return $repository->queryOneById($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function recycleRequestList(Sales $sales, InputRequest $input)
    {
        $task = new ViewRecycleRequestListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }

    #[Query]
    public function recycleRequestDetail(Sales $sales, string $id)
    {
        $task = new ViewRecycleRequestDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }
}
