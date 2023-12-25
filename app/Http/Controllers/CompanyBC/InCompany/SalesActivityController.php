<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\SalesActivity;
use Company\Domain\Model\SalesActivityData;
use Company\Domain\Task\InCompany\SalesActivity\AddSalesActivityTask;
use Company\Domain\Task\InCompany\SalesActivity\SetInitialSalesActivityTask;
use Company\Domain\Task\InCompany\SalesActivity\ViewSalesActivityDetail;
use Company\Domain\Task\InCompany\SalesActivity\ViewSalesActivityList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: SalesActivity::class)]
class SalesActivityController extends Controller
{

    protected function repository(): DoctrineSalesActivityRepository
    {
        return $this->em->getRepository(SalesActivity::class);
    }

    //
    #[Mutation]
    public function setInitialSalesActivity(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();
        
        $task = new SetInitialSalesActivityTask($repository);
        
        $duration = $input->get('duration');
        $payload = new SalesActivityData($this->createLabelData($input), $duration);
        
        $user->executeTaskInCompany($task, $payload);
        return $repository->fetchInitialSalesActivityDetail();
    }
    
    #[Mutation]
    public function addSalesActivity(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();
        
        $task = new AddSalesActivityTask($repository);
        
        $duration = $input->get('duration');
        $payload = new SalesActivityData($this->createLabelData($input), $duration);
        
        $user->executeTaskInCompany($task, $payload);
        return $repository->fetchOneByIdOrDie($payload->id);
    }
    
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewSalesActivityList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewSalesActivityList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        
        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
    
    #[Query]
    public function viewSalesActivityDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewSalesActivityDetail($this->repository());
        $payload = new ViewDetailPayload($id);
        
        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
