<?php

namespace App\Http\Controllers\ManagerBC;

use App\Http\Controllers\Controller;
use App\Http\Controllers\InputRequest;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\RecycleRequest;
use Manager\Domain\Task\RecycleRequest\ApproveRecycleRequest;
use Manager\Domain\Task\RecycleRequest\RejectRecycleRequest;
use Manager\Domain\Task\RecycleRequest\ViewRecycleRequestDetail;
use Manager\Domain\Task\RecycleRequest\ViewRecycleRequestList;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineRecycleRequestRepository;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class RecycleRequestController extends Controller
{

    protected function repository(): DoctrineRecycleRequestRepository
    {
        return $this->em->getRepository(RecycleRequest::class);
    }

    //
    public function approve(ManagerRoleInterface $user, string $recycleRequestId)
    {
        $repository = $this->repository();

        $task = new ApproveRecycleRequest($repository);
        $user->executeManagerTask($task, $recycleRequestId);

        return $repository->fetchOneById($recycleRequestId);
    }

    public function reject(ManagerRoleInterface $user, string $recycleRequestId)
    {
        $repository = $this->repository();

        $task = new RejectRecycleRequest($repository);
        $user->executeManagerTask($task, $recycleRequestId);

        return $repository->fetchOneById($recycleRequestId);
    }

    public function viewList(ManagerRoleInterface $user, InputRequest $input)
    {
        $task = new ViewRecycleRequestList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeManagerTask($task, $payload);

        return $payload->result;
    }

    public function viewDetail(ManagerRoleInterface $user, string $id)
    {
        $task = new ViewRecycleRequestDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeManagerTask($task, $payload);

        return $payload->result;
    }
}
