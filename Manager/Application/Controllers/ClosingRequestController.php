<?php

namespace Manager\Application\Controllers;

use GraphQL\Type\Definition\IntType;
use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales\StrikingAssignment\ClosingRequest;
use Manager\Domain\Model\Manager\Sales\StrikingAssignment\ClosingRequestData;
use Manager\Domain\Task\ClosingRequest\AcceptClosingRequestTask;
use Manager\Domain\Task\ClosingRequest\RejectClosingRequestTask;
use Manager\Domain\Task\ClosingRequest\ViewClosingRequestCount;
use Manager\Domain\Task\ClosingRequest\ViewClosingRequestDetail;
use Manager\Domain\Task\ClosingRequest\ViewClosingRequestList;
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

        $this->executeManagerMutationTask($manager, $task, $payload);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function rejectClosingRequest(Manager $manager, string $id, InputRequest $input)
    {
        $repository = $this->repository();

        $task = new RejectClosingRequestTask($repository);
        $payload = $this->createClosingRequestData($input)
                ->setId($id);

        $this->executeManagerMutationTask($manager, $task, $payload);
        return $repository->queryOneById($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function closingRequestList(Manager $manager, InputRequest $input)
    {
        $task = new ViewClosingRequestList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $this->executeManagerMutationTask($manager, $task, $payload);
        return $payload->result;
    }

    #[Query]
    public function closingRequestDetail(Manager $manager, string $id)
    {
        $task = new ViewClosingRequestDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $this->executeManagerMutationTask($manager, $task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::SUMMARY_RESPONSE_WRAPPER, responseType: IntType::class)]
    public function viewClosingRequestCount(Manager $manager, InputRequest $input)
    {
        $task = new ViewClosingRequestCount($this->repository());
        $payload = $this->buildViewSummaryPayload($input);

        $this->executeManagerMutationTask($manager, $task, $payload);
        return $payload->result;
    }
}
