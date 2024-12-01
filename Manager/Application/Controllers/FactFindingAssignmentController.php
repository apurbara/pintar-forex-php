<?php

namespace Manager\Application\Controllers;

use Manager\Domain\DependencyModel\Customer;
use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales;
use Manager\Domain\Model\Manager\Sales\FactFindingAssignment;
use Manager\Domain\Service\CustomerAssignmentDistributionServiceBuilder;
use Manager\Domain\Task\CustomerAssignment\AssignCustomerListToSalesPayload;
use Manager\Domain\Task\CustomerAssignment\ViewCustomerAssignmentCount;
use Manager\Domain\Task\CustomerAssignment\ViewCustomerAssignmentDetail;
use Manager\Domain\Task\CustomerAssignment\ViewCustomerAssignmentList;
use Manager\Domain\Task\FactFindingAssignment\AssignFactFindingActivityOfCustomerListToSales;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineFactFindingAssignmentRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: FactFindingAssignment::class)]
class FactFindingAssignmentController extends BaseController
{

    protected function repository(): DoctrineFactFindingAssignmentRepository
    {
        return $this->em->getRepository(FactFindingAssignment::class);
    }

    public function assignFactFindingActivityOfCustomerListToSales(Manager $manager, InputRequest $input): void
    {
        $repository = $this->em->getRepository(FactFindingAssignment::class);
        $salesRepository = $this->em->getRepository(Sales::class);
        $customerRepository = $this->em->getRepository(Customer::class);
        $customerAssignmentDistributionService = CustomerAssignmentDistributionServiceBuilder::build($input->get('distributionStrategy'));

        $task = new AssignFactFindingActivityOfCustomerListToSales(
                $repository, $salesRepository, $customerRepository, $customerAssignmentDistributionService);

        $payload = (new AssignCustomerListToSalesPayload());
        foreach ($input->get('salesList') as $salesId) {
            $payload->addSales($salesId);
        }
        foreach ($input->get('customerList') as $customerId) {
            $payload->addCustomer($customerId);
        }
        $this->executeManagerMutationTask($manager, $task, $payload);
    }

    //
    #[Query]
    public function factFindingAssignmentDetail(Manager $manager, string $id)
    {
        $task = new ViewCustomerAssignmentDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $manager->executeTask($task, $payload);
        return $payload->result;
    }

    //
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function factFindingAssignmentList(Manager $manager, InputRequest $input)
    {
        $task = new ViewCustomerAssignmentList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $manager->executeTask($task, $payload);
        return $payload->result;
    }

    //
    public function viewFactFindingAssignmentCount(Manager $manager, InputRequest $input)
    {
        $task = new ViewCustomerAssignmentCount($this->repository());
        $payload = $this->buildViewSummaryPayload($input);

        $manager->executeTask($task, $payload);
        return $payload->result;
    }
}
