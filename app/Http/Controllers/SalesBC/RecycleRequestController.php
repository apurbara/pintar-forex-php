<?php

namespace App\Http\Controllers\SalesBC;

use App\Http\Controllers\Controller;
use App\Http\Controllers\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\RecycleRequest;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\RecycleRequestData;
use Sales\Domain\Task\RecycleRequest\SubmitRecycleRequestTask;
use Sales\Domain\Task\RecycleRequest\UpdateRecycleRequestTask;
use Sales\Domain\Task\RecycleRequest\ViewRecycleRequestDetail;
use Sales\Domain\Task\RecycleRequest\ViewRecycleRequestListTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineRecycleRequestRepository;

class RecycleRequestController extends Controller
{

    protected function repository(): DoctrineRecycleRequestRepository
    {
        return $this->em->getRepository(RecycleRequest::class);
    }

    //
    public function submit(SalesRoleInterface $user, string $assignedCustomerId, InputRequest $input)
    {
        $repository = $this->repository();
        $assignedCustomerRepository = $this->em->getRepository(AssignedCustomer::class);
        $task = new SubmitRecycleRequestTask($repository, $assignedCustomerRepository);

        $note = $input->get('note');
        $payload = (new RecycleRequestData($note))
                ->setAssignedCustomerId($assignedCustomerId);

        $user->executeSalesTask($task, $payload);

        return $repository->fetchOneById($payload->id);
    }

    public function update(SalesRoleInterface $user, string $recycleRequestId, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateRecycleRequestTask($repository);

        $note = $input->get('note');
        $payload = (new RecycleRequestData($note))->setId($recycleRequestId);

        $user->executeSalesTask($task, $payload);

        return $repository->fetchOneById($recycleRequestId);
    }

    public function viewList(SalesRoleInterface $user, InputRequest $input)
    {
        $task = new ViewRecycleRequestListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeSalesTask($task, $payload);

        return $payload->result;
    }

    public function viewDetail(SalesRoleInterface $user, string $id)
    {
        $task = new ViewRecycleRequestDetail($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeSalesTask($task, $payload);

        return $payload->result;
    }
}
