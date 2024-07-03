<?php

namespace Manager\Application\Controllers;

use App\Http\Controllers\Controller;
use GraphQL\Type\Definition\IntType;
use Manager\Application\GraphQL\Object\ClosingRequestMonthlyCountSummaryGraphqlObjectType;
use Manager\Application\GraphQL\Object\ClosingRequestMonthlyTotalTransactionSummaryGraphqlObjectType;
use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment\ClosingRequest;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment\ClosingRequestData;
use Manager\Domain\Task\ClosingRequest\AcceptClosingRequestTask;
use Manager\Domain\Task\ClosingRequest\RejectClosingRequestTask;
use Manager\Domain\Task\ClosingRequest\ViewClosingRequestCount;
use Manager\Domain\Task\ClosingRequest\ViewClosingRequestDetail;
use Manager\Domain\Task\ClosingRequest\ViewClosingRequestList;
use Manager\Domain\Task\ClosingRequest\ViewMonthlyClosingCount;
use Manager\Domain\Task\ClosingRequest\ViewMonthlyTotalClosing;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineClosingRequestRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: ClosingRequest::class)]
class ClosingRequestController extends BaseController
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
    public function acceptClosingRequest(Manager $manager, string $id, InputRequest $input)
    {
        $repository = $this->repository();

        $task = new AcceptClosingRequestTask($repository);
        $payload = $this->createClosingRequestData($input)
                ->setId($id);

        $this->executeManagerTask($manager, $task, $payload);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function rejectClosingRequest(Manager $manager, string $id, InputRequest $input)
    {
        $repository = $this->repository();

        $task = new RejectClosingRequestTask($repository);
        $payload = $this->createClosingRequestData($input)
                ->setId($id);

        $this->executeManagerTask($manager, $task, $payload);
        return $repository->queryOneById($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function closingRequestList(Manager $manager, InputRequest $input)
    {
        $task = new ViewClosingRequestList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $this->executeManagerTask($manager, $task, $payload);
        return $payload->result;
    }

    #[Query]
    public function closingRequestDetail(Manager $manager, string $id)
    {
        $task = new ViewClosingRequestDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $this->executeManagerTask($manager, $task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER,
                responseType: ClosingRequestMonthlyTotalTransactionSummaryGraphqlObjectType::class)]
    public function monthlyTotalTransaction(Manager $manager, InputRequest $input)
    {
        $task = new ViewMonthlyTotalClosing($this->repository());
        $payload = $this->buildViewAllListPayload($input);

        $this->executeManagerTask($manager, $task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER,
                responseType: ClosingRequestMonthlyCountSummaryGraphqlObjectType::class)]
    public function monthlyTransactionCount(Manager $manager, InputRequest $input)
    {
        $task = new ViewMonthlyClosingCount($this->repository());
        $payload = $this->buildViewAllListPayload($input);

        $this->executeManagerTask($manager, $task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::SUMMARY_RESPONSE_WRAPPER, responseType: IntType::class)]
    public function viewClosingRequestCount(Manager $manager, InputRequest $input)
    {
        $task = new ViewClosingRequestCount($this->repository());
        $payload = $this->buildViewSummaryPayload($input);

        $this->executeManagerTask($manager, $task, $payload);
        return $payload->result;
    }
}
