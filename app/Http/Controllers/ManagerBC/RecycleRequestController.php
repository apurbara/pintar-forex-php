<?php

namespace App\Http\Controllers\ManagerBC;

use App\Http\Controllers\Controller;
use App\Http\Controllers\InputRequest;
use Manager\Application\Listener\DistributeRecycledCustomerFromInHouseSalesListener;
use Manager\Domain\Model\AreaStructure\Area\Customer;
use Manager\Domain\Model\CustomerJourney;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer\RecycleRequest;
use Manager\Domain\Service\CustomerAssignmentPriorityCalculatorService;
use Manager\Domain\Task\AssignedCustomer\AssignCustomerToTopPriorityFreelanceSales;
use Manager\Domain\Task\RecycleRequest\ApproveRecycleRequest;
use Manager\Domain\Task\RecycleRequest\RejectRecycleRequest;
use Manager\Domain\Task\RecycleRequest\ViewRecycleRequestDetail;
use Manager\Domain\Task\RecycleRequest\ViewRecycleRequestList;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineRecycleRequestRepository;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Event\Dispatcher;
use Resources\Infrastructure\Persistence\Doctrine\DoctrineTransactionalSession;
use Sales\Application\Listener\InitiateSalesActivityScheduleListener;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer as AssignedCustomer2;
use Sales\Domain\Model\SalesActivity;
use SharedContext\Domain\Event\CustomerAssignedEvent;
use SharedContext\Domain\Event\InHouseSalesCustomerAssignmentRecycledEvent;

class RecycleRequestController extends Controller
{

    protected function repository(): DoctrineRecycleRequestRepository
    {
        return $this->em->getRepository(RecycleRequest::class);
    }
    
    protected function buildInitiateSalesActivityScheduleListener(): InitiateSalesActivityScheduleListener
    {
        $assignedCustomerRepository = $this->em->getRepository(AssignedCustomer2::class);
        $salesActivityRepository = $this->em->getRepository(SalesActivity::class);
        return new InitiateSalesActivityScheduleListener($assignedCustomerRepository, $salesActivityRepository);
    }

    //
    public function approve(ManagerRoleInterface $user, string $recycleRequestId)
    {
        $repository = $this->repository();
        $dispatcher = new Dispatcher();

        $assignedCustomerRepository = $this->em->getRepository(AssignedCustomer::class);
        $customerRepository = $this->em->getRepository(Customer::class);
        $customerJourneyRepository = $this->em->getRepository(CustomerJourney::class);
        $assignmentPriorityCalculator = new CustomerAssignmentPriorityCalculatorService();
        
        $assignCustomerToTopPriorityFreelanceSalesTask = new AssignCustomerToTopPriorityFreelanceSales(
                $assignedCustomerRepository, $customerRepository, $customerJourneyRepository,
                $assignmentPriorityCalculator, $dispatcher);
        $assignedCustomerListener = new DistributeRecycledCustomerFromInHouseSalesListener(
                $user->buildExecuteManagerTaskService(), $assignCustomerToTopPriorityFreelanceSalesTask,
                $user->getPersonnelId(), $user->getManagerId());
        
        $dispatcher->addTransactionalListener(InHouseSalesCustomerAssignmentRecycledEvent::eventName(), $assignedCustomerListener);
        $dispatcher->addTransactionalListener(CustomerAssignedEvent::eventName(), $this->buildInitiateSalesActivityScheduleListener());
        
        $acceptRecycleRequestFunction = function() use($user, $repository, $recycleRequestId, $dispatcher) {
            $task = new ApproveRecycleRequest($repository, $dispatcher);
            $user->executeManagerTask($task, $recycleRequestId);
            $dispatcher->publishTransactional();
            $dispatcher->publishTransactional();
        };
        
        $transactionalSession = new DoctrineTransactionalSession($this->em);
        $transactionalSession->executeAtomically($acceptRecycleRequestFunction);
        
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
