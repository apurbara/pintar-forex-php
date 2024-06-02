<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use App\Http\GraphQL\CompanyBC\Object\ClosingRequestMonthlyCountSummaryGraphqlObjectType;
use App\Http\GraphQL\CompanyBC\Object\ClosingRequestMonthlyTotalTransactionSummaryGraphqlObjectType;
use Company\Domain\Model\Sales\CustomerAssignment\ClosingRequest;
use Company\Domain\Model\Sales\CustomerAssignment\ClosingRequestData;
use Company\Domain\Task\InCompany\ClosingRequest\AcceptClosingRequestTask;
use Company\Domain\Task\InCompany\ClosingRequest\RejectClosingRequestTask;
use Company\Domain\Task\InCompany\ClosingRequest\ViewClosingRequestDetail;
use Company\Domain\Task\InCompany\ClosingRequest\ViewClosingRequestList;
use Company\Domain\Task\InCompany\ClosingRequest\ViewMonthlyClosingCount;
use Company\Domain\Task\InCompany\ClosingRequest\ViewMonthlyTotalClosing;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineClosingRequestRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: ClosingRequest::class)]
class ClosingRequestController extends Controller
{

    protected function repository(): DoctrineClosingRequestRepository
    {
        return $this->em->getRepository(ClosingRequest::class);
    }

    private function createClosingRequestData(InputRequest $input): ClosingRequestData
    {
        return (new ClosingRequestData())
                        ->setRemark($input->get('remark'));
    }

    //
    #[Mutation]
    public function acceptClosingRequest(CompanyUserRoleInterface $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();

        $task = new AcceptClosingRequestTask($repository);
        $payload = $this->createClosingRequestData($input)
                ->setId($id);

        $user->executeTaskInCompany($task, $payload);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function rejectClosingRequest(CompanyUserRoleInterface $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();

        $task = new RejectClosingRequestTask($repository);
        $payload = $this->createClosingRequestData($input)
                ->setId($id);

        $user->executeTaskInCompany($task, $payload);
        return $repository->queryOneById($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function closingRequestList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewClosingRequestList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query]
    public function closingRequestDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewClosingRequestDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER,
                responseType: ClosingRequestMonthlyTotalTransactionSummaryGraphqlObjectType::class)]
    public function monthlyTotalTransaction(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewMonthlyTotalClosing($this->repository());
        $payload = $this->buildViewAllListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER,
                responseType: ClosingRequestMonthlyCountSummaryGraphqlObjectType::class)]
    public function monthlyTransactionCount(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewMonthlyClosingCount($this->repository());
        $payload = $this->buildViewAllListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
