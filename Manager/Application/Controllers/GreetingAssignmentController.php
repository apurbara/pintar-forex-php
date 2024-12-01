<?php

namespace Manager\Application\Controllers;

use Manager\Domain\DependencyModel\Customer;
use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales;
use Manager\Domain\Model\Manager\Sales\GreetingAssignment;
use Manager\Domain\Service\CustomerAssignmentDistributionServiceBuilder;
use Manager\Domain\Task\CustomerAssignment\AssignCustomerListToSalesPayload;
use Manager\Domain\Task\CustomerAssignment\ViewCustomerAssignmentCount;
use Manager\Domain\Task\CustomerAssignment\ViewCustomerAssignmentDetail;
use Manager\Domain\Task\CustomerAssignment\ViewCustomerAssignmentList;
use Manager\Domain\Task\GreetingAssignment\AssignGreetingActivityOfCustomerListToSales;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineGreetingAssignmentRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: GreetingAssignment::class)]
class GreetingAssignmentController extends BaseController
{

    protected function repository(): DoctrineGreetingAssignmentRepository
    {
        return $this->em->getRepository(GreetingAssignment::class);
    }
    
    public function assignGreetingActivityOfCustomerListToSales(Manager $manager, InputRequest $input): void
    {
        $repository = $this->em->getRepository(GreetingAssignment::class);
        $salesRepository = $this->em->getRepository(Sales::class);
        $customerRepository = $this->em->getRepository(Customer::class);
        $customerAssignmentDistributionService = CustomerAssignmentDistributionServiceBuilder::build($input->get('distributionStrategy'));

        $task = new AssignGreetingActivityOfCustomerListToSales(
                $repository, $salesRepository, $customerRepository, $customerAssignmentDistributionService);

        $payload = (new AssignCustomerListToSalesPayload());
        foreach ($input->get('salesList') as $salesId) {
            $payload->addSales($salesId);
        }
        foreach ($input->get('customerList') as $customerId) {
            $payload->addCustomer($customerId);
        }

        $manager->executeTask($task, $payload);
        $this->em->flush();
    }

    //
    #[Query]
    public function greetingAssignmentDetail(Manager $manager, string $id)
    {
        $task = new ViewCustomerAssignmentDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $manager->executeTask($task, $payload);
        return $payload->result;
    }

    //
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function greetingAssignmentList(Manager $manager, InputRequest $input)
    {
        $task = new ViewCustomerAssignmentList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $manager->executeTask($task, $payload);
        return $payload->result;
    }

    //
    public function viewGreetingAssignmentCount(Manager $manager, InputRequest $input)
    {
        $task = new ViewCustomerAssignmentCount($this->repository());
        $payload = $this->buildViewSummaryPayload($input);

        $manager->executeTask($task, $payload);
        return $payload->result;
    }
}
