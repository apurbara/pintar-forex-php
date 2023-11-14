<?php

namespace App\Http\Controllers\SalesBC;

use App\Http\Controllers\Controller;
use App\Http\Controllers\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Domain\TaskPayload\ViewSummaryPayload;
use Resources\Event\Dispatcher;
use Sales\Domain\Model\AreaStructure\Area;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\CustomerJourney;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomerData;
use Sales\Domain\Task\AssignedCustomer\RegisterNewCustomerPayload;
use Sales\Domain\Task\AssignedCustomer\RegisterNewCustomerTask;
use Sales\Domain\Task\AssignedCustomer\UpdateJourney;
use Sales\Domain\Task\AssignedCustomer\ViewAssignedCustomerDetail;
use Sales\Domain\Task\AssignedCustomer\ViewAssignedCustomerList;
use Sales\Domain\Task\AssignedCustomer\ViewTotalCustomerAssignment;
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
        $customerJourneyRepository = $this->em->getRepository(CustomerJourney::class);
        $dispatcher = new Dispatcher();

        $task = new RegisterNewCustomerTask($repository, $areaRepository, $customerRepository, $customerJourneyRepository, $dispatcher);

        $areaId = $input->get('areaId');
        $name = $input->get('name');
        $email = $input->get('email');
        $phone = $input->get('phone');
        $customerData = new Area\CustomerData($name, $email, $phone);
        $payload = new RegisterNewCustomerPayload($areaId, $customerData);
        $user->executeSalesTask($task, $payload);

        return $repository->fetchOneByIdOrDie($payload->id);
    }

    public function updateJourney(SalesRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();
        $customerJourneyRepository = $this->em->getRepository(CustomerJourney::class);
        $task = new UpdateJourney($repository, $customerJourneyRepository);
        $payload = (new AssignedCustomerData())
                ->setId($input->get('id'))
                ->setCustomerJourneyId($input->get('customerJourneyId'));
        $user->executeSalesTask($task, $payload);
        
        return $this->repository()->fetchOneById($payload->id);
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
    
    public function viewTotalCustomerAssignment(SalesRoleInterface $user, InputRequest $input)
    {
        $task = new ViewTotalCustomerAssignment($this->repository());
        $searchSchema =[
            'filters' => $input->get('filters'),
        ];
        $payload = new ViewSummaryPayload($searchSchema);
        
        $user->executeSalesTask($task, $payload);
        return $payload->result;
    }
}
