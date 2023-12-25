<?php

namespace App\Http\Controllers\SalesBC;

use App\Http\Controllers\Controller;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\RecycleRequest;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\RecycleRequestData;
use Sales\Domain\Task\RecycleRequest\SubmitRecycleRequestTask;
use Sales\Domain\Task\RecycleRequest\UpdateRecycleRequestTask;
use Sales\Domain\Task\RecycleRequest\ViewRecycleRequestDetail;
use Sales\Domain\Task\RecycleRequest\ViewRecycleRequestListTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineRecycleRequestRepository;

#[GraphqlMapableController(entity: RecycleRequest::class)]
class RecycleRequestController extends Controller
{

    protected function repository(): DoctrineRecycleRequestRepository
    {
        return $this->em->getRepository(RecycleRequest::class);
    }

    //
    #[Mutation]
    public function submitRecycleRequest(SalesRoleInterface $user, string $AssignedCustomer_id, InputRequest $input)
    {
        $repository = $this->repository();
        $assignedCustomerRepository = $this->em->getRepository(AssignedCustomer::class);
        $task = new SubmitRecycleRequestTask($repository, $assignedCustomerRepository);

        $note = $input->get('note');
        $payload = (new RecycleRequestData($note))
                ->setAssignedCustomerId($AssignedCustomer_id);

        $user->executeSalesTask($task, $payload);

        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateRecycleRequest(SalesRoleInterface $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateRecycleRequestTask($repository);

        $note = $input->get('note');
        $payload = (new RecycleRequestData($note))->setId($id);

        $user->executeSalesTask($task, $payload);

        return $repository->queryOneById($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function recycleRequestList(SalesRoleInterface $user, InputRequest $input)
    {
        $task = new ViewRecycleRequestListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeSalesTask($task, $payload);

        return $payload->result;
    }

    #[Query]
    public function recycleRequestDetail(SalesRoleInterface $user, string $id)
    {
        $task = new ViewRecycleRequestDetail($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeSalesTask($task, $payload);

        return $payload->result;
    }
}
