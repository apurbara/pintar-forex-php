<?php

namespace Manager\Application\Controllers;

use GraphQL\Type\Definition\IntType;
use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment\RecycleRequest;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment\RecycleRequestData;
use Manager\Domain\Task\RecycleRequest\ApproveRecycleRequest;
use Manager\Domain\Task\RecycleRequest\RejectRecycleRequest;
use Manager\Domain\Task\RecycleRequest\ViewMonthlyRecycledCount;
use Manager\Domain\Task\RecycleRequest\ViewRecycleRequestCount;
use Manager\Domain\Task\RecycleRequest\ViewRecycleRequestDetail;
use Manager\Domain\Task\RecycleRequest\ViewRecycleRequestList;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineRecycleRequestRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Event\Dispatcher;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: RecycleRequest::class)]
class RecycleRequestController extends BaseController
{

    protected function repository(): DoctrineRecycleRequestRepository
    {
        return $this->em->getRepository(RecycleRequest::class);
    }

    private function createRecycleRequestData(InputRequest $input): RecycleRequestData
    {
        return (new RecycleRequestData())
                        ->setRemark($input->get('remark'));
    }

    #[Mutation]
    public function approveRecycleRequest(Manager $manager, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $dispatcher = new Dispatcher();
        $task = new ApproveRecycleRequest($repository, $dispatcher);
        $payload = $this->createRecycleRequestData($input)
                ->setId($id);
        
        $this->executeManagerTask($manager, $task, $payload);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function rejectRecycleRequest(Manager $manager, string $id, InputRequest $input)
    {
        $repository = $this->repository();

        $task = new RejectRecycleRequest($repository);
        $payload = $this->createRecycleRequestData($input)
                ->setId($id);
        
        $this->executeManagerTask($manager, $task, $payload);
        return $repository->queryOneById($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function recycleRequestList(Manager $manager, InputRequest $input)
    {
        $task = new ViewRecycleRequestList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $this->executeManagerTask($manager, $task, $payload);
        return $payload->result;
    }

    #[Query]
    public function recycleRequestDetail(Manager $manager, string $id)
    {
        $task = new ViewRecycleRequestDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $this->executeManagerTask($manager, $task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER)]
    public function monthlyRecycledCount(Manager $manager, InputRequest $input)
    {
        $task = new ViewMonthlyRecycledCount($this->repository());
        $payload = $this->buildViewAllListPayload($input);

        $this->executeManagerTask($manager, $task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper:Query::SUMMARY_RESPONSE_WRAPPER,responseType:IntType::class)]
    public function viewRecycleRequestCount(Manager $manager, InputRequest $input)
    {
        $task = new ViewRecycleRequestCount($this->repository());
        $payload = $this->buildViewSummaryPayload($input);

        $this->executeManagerTask($manager, $task, $payload);
        return $payload->result;
    }
}
