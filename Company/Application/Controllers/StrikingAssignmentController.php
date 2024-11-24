<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CompanyUser;
use Company\Domain\Model\Customer;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Manager\Sales\StrikingAssignment;
use Company\Domain\Service\CustomerAssignmentDistributionServiceBuilder;
use Company\Domain\Task\CustomerAssignment\AssignCustomerListToSalesPayload;
use Company\Domain\Task\CustomerAssignment\AssignStrikingActivityOfCustomerListToSales;
use Company\Domain\Task\CustomerAssignment\ViewCustomerAssignmentCount;
use Company\Domain\Task\CustomerAssignment\ViewCustomerAssignmentDetail;
use Company\Domain\Task\CustomerAssignment\ViewCustomerAssignmentList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineStrikingAssignmentRepository;
use GraphQL\Type\Definition\IntType;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: StrikingAssignment::class)]
class StrikingAssignmentController extends BaseController
{

    protected function repository(): DoctrineStrikingAssignmentRepository
    {
        return $this->em->getRepository(StrikingAssignment::class);
    }

    public function assignStrikingActivityOfCustomerListToSales(CompanyUser $user, InputRequest $input): void
    {
        $repository = $this->em->getRepository(StrikingAssignment::class);
        $salesRepository = $this->em->getRepository(Sales::class);
        $customerRepository = $this->em->getRepository(Customer::class);
        $customerAssignmentDistributionService = CustomerAssignmentDistributionServiceBuilder::build($input->get('distributionStrategy'));
        $customerJourneyRepository = $this->em->getRepository(CustomerJourney::class);

        $task = new AssignStrikingActivityOfCustomerListToSales(
                $repository, $salesRepository, $customerRepository, $customerAssignmentDistributionService,
                $customerJourneyRepository);

        $payload = (new AssignCustomerListToSalesPayload());
        foreach ($input->get('salesList') as $salesId) {
            $payload->addSales($salesId);
        }
        foreach ($input->get('customerList') as $customerId) {
            $payload->addCustomer($customerId);
        }

        $this->executeMutationTaskInCompany($user, $task, $payload);
    }

    //
    #[Query]
    public function strikingAssignmentDetail(CompanyUser $user, string $id)
    {
        $task = new ViewCustomerAssignmentDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    //
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function strikingAssignmentList(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewCustomerAssignmentList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    //
    public function viewStrikingAssignmentCount(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewCustomerAssignmentCount($this->repository());
        $payload = $this->buildViewSummaryPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
