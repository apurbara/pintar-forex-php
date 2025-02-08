<?php

namespace Manager\Application\Controllers;

use GraphQL\Type\Definition\IntType;
use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales\FactFindingAssignment\ClosingRequestByFactFinder;
use Manager\Domain\Model\Manager\Sales\FactFindingAssignment\ClosingRequestByFactFinderData;
use Manager\Domain\Task\ClosingRequestByFactFinder\AcceptClosingRequestByFactFinderTask;
use Manager\Domain\Task\ClosingRequestByFactFinder\RejectClosingRequestByFactFinderTask;
use Manager\Domain\Task\ClosingRequestByFactFinder\ViewClosingRequestByFactFinderCount;
use Manager\Domain\Task\ClosingRequestByFactFinder\ViewClosingRequestByFactFinderDetail;
use Manager\Domain\Task\ClosingRequestByFactFinder\ViewClosingRequestByFactFinderList;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineClosingRequestByFactFinderRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: ClosingRequestByFactFinder::class)]
class ClosingRequestByFactFinderController extends BaseController
{

    protected function repository(): DoctrineClosingRequestByFactFinderRepository
    {
        return $this->em->getRepository(ClosingRequestByFactFinder::class);
    }

    private function createClosingRequestByFactFinderData(InputRequest $input): ClosingRequestByFactFinderData
    {
        return (new ClosingRequestByFactFinderData())
                        ->setRemark($input->get('remark'));
    }

    //
    #[Mutation]
    public function acceptClosingRequestByFactFinder(Manager $manager, string $id, InputRequest $input)
    {
        $repository = $this->repository();

        $task = new AcceptClosingRequestByFactFinderTask($repository);
        $payload = $this->createClosingRequestByFactFinderData($input)
                ->setId($id);

        $this->executeManagerMutationTask($manager, $task, $payload);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function rejectClosingRequestByFactFinder(Manager $manager, string $id, InputRequest $input)
    {
        $repository = $this->repository();

        $task = new RejectClosingRequestByFactFinderTask($repository);
        $payload = $this->createClosingRequestByFactFinderData($input)
                ->setId($id);

        $this->executeManagerMutationTask($manager, $task, $payload);
        return $repository->queryOneById($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function closingRequestByFactFinderList(Manager $manager, InputRequest $input)
    {
        $task = new ViewClosingRequestByFactFinderList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $this->executeManagerMutationTask($manager, $task, $payload);
        return $payload->result;
    }

    #[Query]
    public function closingRequestByFactFinderDetail(Manager $manager, string $id)
    {
        $task = new ViewClosingRequestByFactFinderDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $this->executeManagerMutationTask($manager, $task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::SUMMARY_RESPONSE_WRAPPER, responseType: IntType::class)]
    public function viewClosingRequestByFactFinderCount(Manager $manager, InputRequest $input)
    {
        $task = new ViewClosingRequestByFactFinderCount($this->repository());
        $payload = $this->buildViewSummaryPayload($input);

        $this->executeManagerMutationTask($manager, $task, $payload);
        return $payload->result;
    }
}
