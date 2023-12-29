<?php

namespace App\Http\Controllers\ManagerBC;

use App\Http\Controllers\Controller;
use App\Http\GraphQL\ManagerBC\Object\ClosingRequestMonthlyCountSummaryGraphqlObjectType;
use App\Http\GraphQL\ManagerBC\Object\ClosingRequestMonthlyTotalTransactionSummaryGraphqlObjectType;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\ClosingRequest;
use Manager\Domain\Task\ClosingRequest\AcceptClosingRequestTask;
use Manager\Domain\Task\ClosingRequest\RejectClosingRequestTask;
use Manager\Domain\Task\ClosingRequest\ViewClosingRequestDetail;
use Manager\Domain\Task\ClosingRequest\ViewClosingRequestList;
use Manager\Domain\Task\ClosingRequest\ViewMonthlyClosingCount;
use Manager\Domain\Task\ClosingRequest\ViewMonthlyTotalClosing;
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
    
    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER, responseType: ClosingRequestMonthlyTotalTransactionSummaryGraphqlObjectType::class)]
    public function monthlyTotalTransaction(ManagerRoleInterface $user, InputRequest $input)
    {
        $task = new ViewMonthlyTotalClosing($this->repository());
        $payload = $this->buildViewAllListPayload($input);
        $user->executeManagerTask($task, $payload);
        
        return $payload->result;
    }
    
    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER, responseType: ClosingRequestMonthlyCountSummaryGraphqlObjectType::class)]
    public function monthlyTransactionCount(ManagerRoleInterface $user, InputRequest $input)
    {
        $task = new ViewMonthlyClosingCount($this->repository());
        $payload = $this->buildViewAllListPayload($input);
        $user->executeManagerTask($task, $payload);
        
        return $payload->result;
    }
}
