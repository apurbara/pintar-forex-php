<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CompanyUser;
use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Domain\Task\CustomerAssignment\ViewCustomerAssignmentCount;
use Company\Domain\Task\CustomerAssignment\ViewCustomerAssignmentDetail;
use Company\Domain\Task\CustomerAssignment\ViewCustomerAssignmentList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerAssignmentRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: CustomerAssignment::class)]
class CustomerAssignmentController extends BaseController
{

    protected function repository(): DoctrineCustomerAssignmentRepository
    {
        return $this->em->getRepository(CustomerAssignment::class);
    }

    //
    #[Query]
    public function customerAssignmentDetail(CompanyUser $user, string $id)
    {
        $task = new ViewCustomerAssignmentDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    //
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function customerAssignmentList(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewCustomerAssignmentList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    //
    public function viewCustomerAssignmentCount(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewCustomerAssignmentCount($this->repository());
        $payload = $this->buildViewSummaryPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
