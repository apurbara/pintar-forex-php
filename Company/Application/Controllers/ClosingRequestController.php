<?php

namespace Company\Application\Controllers;

use Company\Application\GraphQL\Object\ClosingRequestMonthlyCountSummaryGraphqlObjectType;
use Company\Application\GraphQL\Object\ClosingRequestMonthlyTotalTransactionSummaryGraphqlObjectType;
use Company\Domain\Model\CompanyUser;
use Company\Domain\Model\Manager\Sales\CustomerAssignment\ClosingRequest;
use Company\Domain\Task\ClosingRequest\ViewClosingRequestCount;
use Company\Domain\Task\ClosingRequest\ViewClosingRequestDetail;
use Company\Domain\Task\ClosingRequest\ViewClosingRequestList;
use Company\Domain\Task\ClosingRequest\ViewMonthlyClosingCount;
use Company\Domain\Task\ClosingRequest\ViewMonthlyTotalClosing;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineClosingRequestRepository;
use GraphQL\Type\Definition\IntType;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: ClosingRequest::class)]
class ClosingRequestController extends BaseController
{

    protected function repository(): DoctrineClosingRequestRepository
    {
        return $this->em->getRepository(ClosingRequest::class);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function closingRequestList(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewClosingRequestList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query]
    public function closingRequestDetail(CompanyUser $user, string $id)
    {
        $task = new ViewClosingRequestDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER,
                responseType: ClosingRequestMonthlyTotalTransactionSummaryGraphqlObjectType::class)]
    public function monthlyTotalTransaction(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewMonthlyTotalClosing($this->repository());
        $payload = $this->buildViewAllListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER,
                responseType: ClosingRequestMonthlyCountSummaryGraphqlObjectType::class)]
    public function monthlyTransactionCount(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewMonthlyClosingCount($this->repository());
        $payload = $this->buildViewAllListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::SUMMARY_RESPONSE_WRAPPER, responseType: IntType::class)]
    public function viewClosingRequestCount(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewClosingRequestCount($this->repository());
        $payload = $this->buildViewSummaryPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
