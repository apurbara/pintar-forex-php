<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\AreaStructure\Area\Customer;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Sales;
use Company\Domain\Model\Sales\CustomerAssignment;
use Company\Domain\Service\CustomerAssignmentDistributionServiceBuilder;
use Company\Domain\Task\InCompany\CustomerAssignment\AssignCustomerListToSales;
use Company\Domain\Task\InCompany\CustomerAssignment\AssignCustomerListToSalesPayload;
use Company\Domain\Task\InCompany\CustomerAssignment\ViewCustomerAssignmentDetail;
use Company\Domain\Task\InCompany\CustomerAssignment\ViewCustomerAssignmentList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerAssignmentRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Event\Dispatcher;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Application\Listener\AllocateInitialSalesActivityScheduleForMultipleAssignmentListener;
use SharedContext\Domain\Event\MultipleCustomerAssignmentReceivedBySales;

#[GraphqlMapableController(entity: CustomerAssignment::class)]
class CustomerAssignmentController extends Controller
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
        $listener = new AllocateInitialSalesActivityScheduleForMultipleAssignmentListener(
                $salesRepository, $salesActivityScheduleRepository, $customerAssignmentRepository,
                $salesActivityRepository);
        $dispatcher->addAsynchronousListener($eventName, $listener);
    }

    public function assignedMultipleCustomerToMultipleSales(CompanyUserRoleInterface $user, InputRequest $input): void
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

        $user->executeTaskInCompany($task, $payload);
        $dispatcher->publishAsynchronous();
    }

    //
    #[Query]
    public function customerAssignmentDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewCustomerAssignmentDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    //
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function customerAssignmentList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewCustomerAssignmentList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
