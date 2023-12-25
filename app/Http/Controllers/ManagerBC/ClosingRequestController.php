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

class ClosingRequestController extends Controller
{

    protected function repository(): DoctrineClosingRequestRepository
    {
        return $this->em->getRepository(ClosingRequest::class);
    }

    //
    public function accept(ManagerRoleInterface $user, string $closingRequestId)
    {
        $repository = $this->repository();

        $task = new AcceptClosingRequestTask($repository);
        $user->executeManagerTask($task, $closingRequestId);

        return $repository->fetchOneById($closingRequestId);
    }

    public function reject(ManagerRoleInterface $user, string $closingRequestId)
    {
        $repository = $this->repository();

        $task = new RejectClosingRequestTask($repository);
        $user->executeManagerTask($task, $closingRequestId);

        return $repository->fetchOneById($closingRequestId);
    }

    public function viewList(ManagerRoleInterface $user, InputRequest $input)
    {
        $task = new ViewClosingRequestList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeManagerTask($task, $payload);

        return $payload->result;
    }

    public function viewDetail(ManagerRoleInterface $user, string $id)
    {
        $task = new ViewClosingRequestDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeManagerTask($task, $payload);

        return $payload->result;
    }
}
