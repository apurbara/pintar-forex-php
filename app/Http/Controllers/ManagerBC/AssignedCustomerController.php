<?php

namespace App\Http\Controllers\ManagerBC;

use App\Http\Controllers\Controller;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Manager\Domain\Task\AssignedCustomer\ViewAssignedCustomerDetail;
use Manager\Domain\Task\AssignedCustomer\ViewAssignedCustomerList;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineAssignedCustomerRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class AssignedCustomerController extends Controller
{

    protected function repository(): DoctrineAssignedCustomerRepository
    {
        return $this->em->getRepository(AssignedCustomer::class);
    }

    //
    protected function viewDetail(ManagerRoleInterface $user, string $id)
    {
        $task = new ViewAssignedCustomerDetail($this->repository());
        $payload = new ViewDetailPayload($id);
        
        $user->executeManagerTask($task, $payload);
        return $payload->result;
    }
    //
    protected function viewList(ManagerRoleInterface $user, InputRequest $input)
    {
        $task = new ViewAssignedCustomerList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        
        $user->executeManagerTask($task, $payload);
        return $payload->result;
    }
}
