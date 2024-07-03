<?php

namespace Manager\Application\Controllers;

use GraphQL\Type\Definition\IntType;
use Manager\Domain\DependencyModel\Customer;
use Manager\Domain\DependencyModel\CustomerJourney;
use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment;
use Manager\Domain\Service\CustomerAssignmentDistributionServiceBuilder;
use Manager\Domain\Task\CustomerAssignment\AssignCustomerListToSales;
use Manager\Domain\Task\CustomerAssignment\AssignCustomerListToSalesPayload;
use Manager\Domain\Task\CustomerAssignment\ViewCustomerAssignmentCount;
use Manager\Domain\Task\CustomerAssignment\ViewCustomerAssignmentDetail;
use Manager\Domain\Task\CustomerAssignment\ViewCustomerAssignmentList;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerAssignmentRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Event\Dispatcher;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use SharedContext\Domain\Event\MultipleCustomerAssignmentReceivedBySales;

#[GraphqlMapableController(entity: CustomerAssignment::class)]
class CustomerAssignmentController extends BaseController
{

    protected function repository(): DoctrineCustomerAssignmentRepository
    {
        return $this->em->getRepository(CustomerAssignment::class);
    }

    //
    private function registerInitiateScheduleListener(Dispatcher $dispatcher): void
    {
        $eventName = MultipleCustomerAssignmentReceivedBySales::NAME;
        $salesRepository = $this->em->getRepository(\Sales\Domain\Model\Sales::class);
        $salesActivityScheduleRepository = $this->em->getRepository(\Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule::class);
        $customerAssignmentRepository = $this->em->getRepository(\Sales\Domain\Model\Sales\CustomerAssignment::class);
        $salesActivityRepository = $this->em->getRepository(\Sales\Domain\DependencyModel\SalesActivity::class);
        $listener = new \Sales\Application\Listener\AllocateInitialSalesActivityScheduleForMultipleAssignmentListener(
                $salesRepository, $salesActivityScheduleRepository, $customerAssignmentRepository,
                $salesActivityRepository);
        $dispatcher->addAsynchronousListener($eventName, $listener);
    }

    public function assignedMultipleCustomerToMultipleSales(Manager $manager, InputRequest $input): void
    {
        $repository = $this->em->getRepository(CustomerAssignment::class);
        $salesRepository = $this->em->getRepository(Sales::class);
        $customerRepository = $this->em->getRepository(Customer::class);
        $customerJourneyRepository = $this->em->getRepository(CustomerJourney::class);
        $customerAssignmentDistributionService = CustomerAssignmentDistributionServiceBuilder::build($input->get('distributionStrategy'));
        $dispatcher = new Dispatcher();
        if ($input->get('initiateSchedules') ?? false) {
            $this->registerInitiateScheduleListener($dispatcher);
        }

        $task = new AssignCustomerListToSales(
                $repository, $salesRepository, $customerRepository, $customerJourneyRepository,
                $customerAssignmentDistributionService, $dispatcher);

        $payload = (new AssignCustomerListToSalesPayload());
        foreach ($input->get('salesList') as $salesId) {
            $payload->addSales($salesId);
        }
        foreach ($input->get('customerList') as $customerId) {
            $payload->addCustomer($customerId);
        }

        $this->executeManagerTask($manager, $task, $payload);
        $dispatcher->publishAsynchronous();
    }

    //
    #[Query]
    public function customerAssignmentDetail(Manager $manager, string $id)
    {
        $task = new ViewCustomerAssignmentDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $this->executeManagerTask($manager, $task, $payload);
        return $payload->result;
    }

    //
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function customerAssignmentList(Manager $manager, InputRequest $input)
    {
        $task = new ViewCustomerAssignmentList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $this->executeManagerTask($manager, $task, $payload);
        return $payload->result;
    }

    //
    #[Query(responseWrapper: Query::SUMMARY_RESPONSE_WRAPPER, responseType: IntType::class)]
    public function viewCustomerAssignmentCount(Manager $manager, InputRequest $input)
    {
        $task = new ViewCustomerAssignmentCount($this->repository());
        $payload = $this->buildViewSummaryPayload($input);

        $this->executeManagerTask($manager, $task, $payload);
        return $payload->result;
    }
}
