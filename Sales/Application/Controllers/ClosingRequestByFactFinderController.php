<?php

namespace Sales\Application\Controllers;

use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\FactFindingAssignment;
use Sales\Domain\Model\Sales\FactFindingAssignment\ClosingRequestByFactFinder;
use Sales\Domain\Model\Sales\FactFindingAssignment\ClosingRequestByFactFinderData;
use Sales\Domain\Task\ClosingRequestByFactFinder\SubmitClosingRequestByFactFinderTask;
use Sales\Domain\Task\ClosingRequestByFactFinder\UpdateClosingRequestByFactFinderTask;
use Sales\Domain\Task\ClosingRequestByFactFinder\ViewClosingRequestByFactFinderDetail;
use Sales\Domain\Task\ClosingRequestByFactFinder\ViewClosingRequestByFactFinderListTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineClosingRequestByFactFinderRepository;

#[GraphqlMapableController(entity: ClosingRequestByFactFinder::class)]
class ClosingRequestByFactFinderController extends BaseController
{

    protected function repository(): DoctrineClosingRequestByFactFinderRepository
    {
        return $this->em->getRepository(ClosingRequestByFactFinder::class);
    }

    //
    #[Mutation]
    public function submitClosingRequestByFactFinder(Sales $sales, InputRequest $input)
    {
        $repository = $this->repository();
        $factFindingAssignmentRepository = $this->em->getRepository(FactFindingAssignment::class);
        $task = new SubmitClosingRequestByFactFinderTask($repository, $factFindingAssignmentRepository);

        $transactionValue = $input->get('transactionValue');
        $note = $input->get('note');
        $payload = (new ClosingRequestByFactFinderData($transactionValue, $note))
                ->setFactFindingAssignmentId($input->get('FactFindingAssignment_id'));

        $this->executeSalesMutationTask($sales, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateClosingRequestByFactFinder(Sales $sales, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateClosingRequestByFactFinderTask($repository);

        $transactionValue = $input->get('transactionValue');
        $note = $input->get('note');
        $payload = (new ClosingRequestByFactFinderData($transactionValue, $note))->setId($id);

        $this->executeSalesMutationTask($sales, $task, $payload);
        return $repository->queryOneById($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function closingRequestByFactFinderList(Sales $sales, InputRequest $input)
    {
        $task = new ViewClosingRequestByFactFinderListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }

    #[Query]
    public function closingRequestByFactFinderDetail(Sales $sales, string $id)
    {
        $task = new ViewClosingRequestByFactFinderDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }
}
