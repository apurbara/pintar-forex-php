<?php

namespace App\Http\Controllers\ManagerBC;

use App\Http\Controllers\Controller;
use Manager\Domain\Model\AreaStructure\Area\Customer;
use Manager\Domain\Model\CustomerJourney;
use Manager\Domain\Model\Personnel\Manager\Sales;
use Manager\Domain\Model\Personnel\Manager\Sales\AssignedCustomer;
use Manager\Domain\Service\CustomerAssignmentDistributionCalculatorService;
use Manager\Domain\Task\AssignedCustomer\AssignCustomerListToSales;
use Manager\Domain\Task\AssignedCustomer\AssignCustomerListToSalesPayload;
use Manager\Domain\Task\AssignedCustomer\ViewAssignedCustomerDetail;
use Manager\Domain\Task\AssignedCustomer\ViewAssignedCustomerList;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineAssignedCustomerRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Event\Dispatcher;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Application\Listener\AllocateInitialSalesActivityScheduleForMultipleAssignmentListener;
use SharedContext\Domain\Event\MultipleCustomerAssignmentReceivedBySales;

#[GraphqlMapableController(entity: AssignedCustomer::class)]
class AssignedCustomerController extends Controller
{

    protected function repository(): DoctrineAssignedCustomerRepository
    {
        return $this->em->getRepository(AssignedCustomer::class);
    }

    //
    private function registerAllocateInitialSalesActivityScheduleForMultipleAssignmentListener(Dispatcher $dispatcher): void
    {
        $eventName = MultipleCustomerAssignmentReceivedBySales::NAME;
        $salesRepository = $this->em->getRepository(\Sales\Domain\Model\Personnel\Sales::class);
        $salesActivityScheduleRepository = $this->em->getRepository(\Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule::class);
        $assignedCustomerRepository = $this->em->getRepository(\Sales\Domain\Model\Personnel\Sales\AssignedCustomer::class);
        $salesActivityRepository = $this->em->getRepository(\Sales\Domain\Model\SalesActivity::class);
        $listener = new AllocateInitialSalesActivityScheduleForMultipleAssignmentListener(
                $salesRepository, $salesActivityScheduleRepository, $assignedCustomerRepository,
                $salesActivityRepository);
        $dispatcher->addAsynchronousListener($eventName, $listener);
    }

    public function assignedMultipleCustomerToMultipleSales(ManagerRoleInterface $user, InputRequest $input): void
    {
        $assignedCustomerRepository = $this->em->getRepository(AssignedCustomer::class);
        $salesRepository = $this->em->getRepository(Sales::class);
        $customerRepository = $this->em->getRepository(Customer::class);
        $customerJourneyRepository = $this->em->getRepository(CustomerJourney::class);
        $customerAssignmentDistributionCalculatorService = new CustomerAssignmentDistributionCalculatorService();
        $dispatcher = new Dispatcher();
        $this->registerAllocateInitialSalesActivityScheduleForMultipleAssignmentListener($dispatcher);

        $task = new AssignCustomerListToSales($assignedCustomerRepository, $salesRepository, $customerRepository,
                $customerJourneyRepository, $customerAssignmentDistributionCalculatorService, $dispatcher);

        $payload = (new AssignCustomerListToSalesPayload());
        foreach ($input->get('salesList') as $salesId) {
            $payload->addSales($salesId);
        }
        foreach ($input->get('customerList') as $customerId) {
            $payload->addCustomer($customerId);
        }
        
        $user->executeManagerTask($task, $payload);
        $dispatcher->publishAsynchronous();
    }

    //
    #[Query]
    public function assignedCustomerDetail(ManagerRoleInterface $user, string $id)
    {
        $task = new ViewAssignedCustomerDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeManagerTask($task, $payload);
        return $payload->result;
    }

    //
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function assignedCustomerList(ManagerRoleInterface $user, InputRequest $input)
    {
        $task = new ViewAssignedCustomerList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeManagerTask($task, $payload);
        return $payload->result;
    }
}
