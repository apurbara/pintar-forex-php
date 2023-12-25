<?php

namespace App\Http\Controllers\ManagerBC;

use App\Http\Controllers\Controller;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\ClosingRequest;
use Manager\Domain\Task\ClosingRequest\AcceptClosingRequestTask;
use Manager\Domain\Task\ClosingRequest\RejectClosingRequestTask;
use Manager\Domain\Task\ClosingRequest\ViewClosingRequestDetail;
use Manager\Domain\Task\ClosingRequest\ViewClosingRequestList;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineClosingRequestRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: ClosingRequest::class)]
class ClosingRequestController extends Controller
{

    protected function repository(): DoctrineClosingRequestRepository
    {
        return $this->em->getRepository(ClosingRequest::class);
    }

    //
    #[Mutation]
    public function acceptClosingRequest(ManagerRoleInterface $user, string $id)
    {
        $repository = $this->repository();

        $task = new AcceptClosingRequestTask($repository);
        $user->executeManagerTask($task, $id);

        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function rejectClosingRequest(ManagerRoleInterface $user, string $id)
    {
        $repository = $this->repository();

        $task = new RejectClosingRequestTask($repository);
        $user->executeManagerTask($task, $id);

        return $repository->queryOneById($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function closingRequestList(ManagerRoleInterface $user, InputRequest $input)
    {
        $task = new ViewClosingRequestList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeManagerTask($task, $payload);

        return $payload->result;
    }

    #[Query]
    public function closingRequestDetail(ManagerRoleInterface $user, string $id)
    {
        $task = new ViewClosingRequestDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeManagerTask($task, $payload);

        return $payload->result;
    }
}
