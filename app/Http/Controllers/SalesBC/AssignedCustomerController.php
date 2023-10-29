<?php

namespace App\Http\Controllers\SalesBC;

use App\Http\Controllers\Controller;
use App\Http\Controllers\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Event\Dispatcher;
use Sales\Domain\Model\AreaStructure\Area;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Task\AssignedCustomer\RegisterNewCustomerPayload;
use Sales\Domain\Task\AssignedCustomer\RegisterNewCustomerTask;
use Sales\Domain\Task\AssignedCustomer\ViewAssignedCustomerDetail;
use Sales\Domain\Task\AssignedCustomer\ViewAssignedCustomerList;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineAssignedCustomerRepository;

class AssignedCustomerController extends Controller
{

    protected function repository(): DoctrineAssignedCustomerRepository
    {
        return $this->em->getRepository(AssignedCustomer::class);
    }

    //
    public function registerNewCustomer(SalesRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();
        $areaRepository = $this->em->getRepository(Area::class);
        $customerRepository = $this->em->getRepository(Customer::class);
        $dispatcher = new Dispatcher();
        
        $task = new RegisterNewCustomerTask($repository,
                $areaRepository, $customerRepository, $dispatcher);
        
        $areaId = $input->get('areaId');
        $name = $input->get('name');
        $email = $input->get('email');
        $customerData = new Area\CustomerData($name, $email);
        $payload = new RegisterNewCustomerPayload($areaId, $customerData);
        $user->executeSalesTask($task, $payload);
        
        return $repository->fetchOneByIdOrDie($payload->id);
    }
    
    public function viewList(SalesRoleInterface $user, InputRequest $input)
    {
        $task = new ViewAssignedCustomerList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeSalesTask($task, $payload);
        
        return $payload->result;
    }
    
    public function viewDetail(SalesRoleInterface $user, string $assignedCustomerId)
    {
        $task = new ViewAssignedCustomerDetail($this->repository());
        $payload = new ViewDetailPayload($assignedCustomerId);
        $user->executeSalesTask($task, $payload);
        
        return $payload->result;
    }
}
