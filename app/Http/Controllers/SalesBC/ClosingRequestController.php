<?php

namespace App\Http\Controllers\SalesBC;

use App\Http\Controllers\Controller;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\ClosingRequest;
use Sales\Domain\Task\ClosingRequest\SubmitClosingRequestTask;
use Sales\Domain\Task\ClosingRequest\UpdateClosingRequestTask;
use Sales\Domain\Task\ClosingRequest\ViewClosingRequestDetail;
use Sales\Domain\Task\ClosingRequest\ViewClosingRequestListTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineClosingRequestRepository;

class ClosingRequestController extends Controller
{

    protected function repository(): DoctrineClosingRequestRepository
    {
        return $this->em->getRepository(ClosingRequest::class);
    }

    //
    public function submit(SalesRoleInterface $user, string $assignedCustomerId, InputRequest $input)
    {
        $repository = $this->repository();
        $assignedCustomerRepository = $this->em->getRepository(AssignedCustomer::class);
        $task = new SubmitClosingRequestTask($repository, $assignedCustomerRepository);
        
        $transactionValue = $input->get('transactionValue');
        $note = $input->get('note');
        $payload = (new AssignedCustomer\ClosingRequestData($transactionValue, $note))
                ->setAssignedCustomerId($assignedCustomerId);
        
        $user->executeSalesTask($task, $payload);
        
        return $repository->fetchOneById($payload->id);
    }
    
    public function update(SalesRoleInterface $user, string $closingRequestId, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateClosingRequestTask($repository);
        
        $transactionValue = $input->get('transactionValue');
        $note = $input->get('note');
        $payload = (new AssignedCustomer\ClosingRequestData($transactionValue, $note))->setId($closingRequestId);
        
        $user->executeSalesTask($task, $payload);
        
        return $repository->fetchOneById($closingRequestId);
    }
    
    public function viewList(SalesRoleInterface $user, InputRequest $input)
    {
        $task = new ViewClosingRequestListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeSalesTask($task, $payload);
        
        return $payload->result;
    }
    
    public function viewDetail(SalesRoleInterface $user, string $id)
    {
        $task = new ViewClosingRequestDetail($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeSalesTask($task, $payload);
        
        return $payload->result;
    }
}
