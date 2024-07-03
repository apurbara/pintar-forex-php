<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CompanyUser;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\RecycleRequest;
use Company\Domain\Task\RecycleRequest\ViewMonthlyRecycledCount;
use Company\Domain\Task\RecycleRequest\ViewRecycleRequestCount;
use Company\Domain\Task\RecycleRequest\ViewRecycleRequestDetail;
use Company\Domain\Task\RecycleRequest\ViewRecycleRequestList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineRecycleRequestRepository;
use GraphQL\Type\Definition\IntType;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: RecycleRequest::class)]
class RecycleRequestController extends BaseController
{

    protected function repository(): DoctrineRecycleRequestRepository
    {
        return $this->em->getRepository(RecycleRequest::class);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function recycleRequestList(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewRecycleRequestList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query]
    public function recycleRequestDetail(CompanyUser $user, string $id)
    {
        $task = new ViewRecycleRequestDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER)]
    public function monthlyRecycledCount(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewMonthlyRecycledCount($this->repository());
        $payload = $this->buildViewAllListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper:Query::SUMMARY_RESPONSE_WRAPPER,responseType:IntType::class)]
    public function viewRecycleRequestCount(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewRecycleRequestCount($this->repository());
        $payload = $this->buildViewSummaryPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
