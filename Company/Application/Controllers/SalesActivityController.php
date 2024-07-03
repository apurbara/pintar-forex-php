<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CompanyUser;
use Company\Domain\Model\SalesActivity;
use Company\Domain\Model\SalesActivityData;
use Company\Domain\Task\SalesActivity\AddSalesActivityTask;
use Company\Domain\Task\SalesActivity\DisableSalesActivity;
use Company\Domain\Task\SalesActivity\EnableSalesActivity;
use Company\Domain\Task\SalesActivity\SetInitialSalesActivityTask;
use Company\Domain\Task\SalesActivity\UpdateSalesActivity;
use Company\Domain\Task\SalesActivity\ViewSalesActivityDetail;
use Company\Domain\Task\SalesActivity\ViewSalesActivityList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: SalesActivity::class)]
class SalesActivityController extends BaseController
{

    protected function repository(): DoctrineSalesActivityRepository
    {
        return $this->em->getRepository(SalesActivity::class);
    }
    
    private function createData(InputRequest $input): SalesActivityData
    {
        $duration = $input->get('duration');
        return new SalesActivityData($this->createLabelData($input), $duration);
    }

    //
    #[Mutation]
    public function setInitialSalesActivity(CompanyUser $user, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new SetInitialSalesActivityTask($repository);
        $payload = $this->createData($input);
        
        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->fetchInitialSalesActivityDetail();
    }
    
    #[Mutation]
    public function addSalesActivity(CompanyUser $user, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new AddSalesActivityTask($repository);
        $payload = $this->createData($input);
        
        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->fetchOneByIdOrDie($payload->id);
    }
    
    #[Mutation]
    public function updateSalesActivity(CompanyUser $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateSalesActivity($repository);
        $payload = $this->createData($input)
                ->setId($id);
        
        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->fetchOneByIdOrDie($payload->id);
    }
    
    #[Mutation]
    public function disableSalesActivity(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new DisableSalesActivity($repository);
        
        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->fetchOneByIdOrDie($id);
    }
    
    #[Mutation]
    public function enableSalesActivity(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new EnableSalesActivity($repository);
        
        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->fetchOneByIdOrDie($id);
    }
    
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function salesActivityList(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewSalesActivityList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        
        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
    
    #[Query]
    public function salesActivityDetail(CompanyUser $user, string $id)
    {
        $task = new ViewSalesActivityDetail($this->repository());
        $payload = new ViewDetailPayload($id);
        
        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
