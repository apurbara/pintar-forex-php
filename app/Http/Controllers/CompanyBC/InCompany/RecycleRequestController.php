<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\Personnel\Sales\CustomerAssignment\RecycleRequest;
use Company\Domain\Model\Personnel\Sales\CustomerAssignment\RecycleRequestData;
use Company\Domain\Task\InCompany\RecycleRequest\ApproveRecycleRequest;
use Company\Domain\Task\InCompany\RecycleRequest\RejectRecycleRequest;
use Company\Domain\Task\InCompany\RecycleRequest\ViewMonthlyRecycledCount;
use Company\Domain\Task\InCompany\RecycleRequest\ViewRecycleRequestDetail;
use Company\Domain\Task\InCompany\RecycleRequest\ViewRecycleRequestList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineRecycleRequestRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Event\Dispatcher;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: RecycleRequest::class)]
class RecycleRequestController extends Controller
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
    public function approveRecycleRequest(CompanyUserRoleInterface $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $dispatcher = new Dispatcher();
        $task = new ApproveRecycleRequest($repository, $dispatcher);
        $payload = $this->createRecycleRequestData($input)
                ->setId($id);
        
        $user->executeTaskInCompany($task, $payload);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function rejectRecycleRequest(CompanyUserRoleInterface $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();

        $task = new RejectRecycleRequest($repository);
        $payload = $this->createRecycleRequestData($input)
                ->setId($id);
        
        $user->executeTaskInCompany($task, $payload);
        return $repository->queryOneById($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function recycleRequestList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewRecycleRequestList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query]
    public function recycleRequestDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewRecycleRequestDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER)]
    public function monthlyRecycledCount(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewMonthlyRecycledCount($this->repository());
        $payload = $this->buildViewAllListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
