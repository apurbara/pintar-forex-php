<?php

namespace App\Http\Controllers\SalesBC\BySales;

use App\Http\Controllers\Controller;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignment\ClosingRequest;
use Sales\Domain\Model\Sales\CustomerAssignment\ClosingRequestData;
use Sales\Domain\Task\BySales\ClosingRequest\SubmitClosingRequestTask;
use Sales\Domain\Task\BySales\ClosingRequest\UpdateClosingRequestTask;
use Sales\Domain\Task\BySales\ClosingRequest\ViewClosingRequestDetail;
use Sales\Domain\Task\BySales\ClosingRequest\ViewClosingRequestListTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineClosingRequestRepository;

#[GraphqlMapableController(entity: ClosingRequest::class)]
class ClosingRequestController extends Controller
{

    protected function repository(): DoctrineClosingRequestRepository
    {
        return $this->em->getRepository(ClosingRequest::class);
    }

    //
    #[Mutation]
    public function submitClosingRequest(SalesRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();
        $customerAssignmentRepository = $this->em->getRepository(CustomerAssignment::class);
        $task = new SubmitClosingRequestTask($repository, $customerAssignmentRepository);

        $transactionValue = $input->get('transactionValue');
        $note = $input->get('note');
        $payload = (new ClosingRequestData($transactionValue, $note))
                ->setCustomerAssignmentId($input->get('CustomerAssignment_id'));

        $user->executeSalesTask($task, $payload);

        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateClosingRequest(SalesRoleInterface $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateClosingRequestTask($repository);

        $transactionValue = $input->get('transactionValue');
        $note = $input->get('note');
        $payload = (new ClosingRequestData($transactionValue, $note))->setId($id);

        $user->executeSalesTask($task, $payload);

        return $repository->queryOneById($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function closingRequestList(SalesRoleInterface $user, InputRequest $input)
    {
        $task = new ViewClosingRequestListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeSalesTask($task, $payload);

        return $payload->result;
    }

    #[Query]
    public function closingRequestDetail(SalesRoleInterface $user, string $id)
    {
        $task = new ViewClosingRequestDetail($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeSalesTask($task, $payload);

        return $payload->result;
    }
}
