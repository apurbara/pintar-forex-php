<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use App\Http\Controllers\InputRequest;
use Company\Domain\Model\SalesActivity;
use Company\Domain\Model\SalesActivityData;
use Company\Domain\Task\InCompany\SalesActivity\AddSalesActivityTask;
use Company\Domain\Task\InCompany\SalesActivity\SetInitialSalesActivityTask;
use Company\Domain\Task\InCompany\SalesActivity\ViewSalesActivityDetail;
use Company\Domain\Task\InCompany\SalesActivity\ViewSalesActivityList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityRepository;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class SalesActivityController extends Controller
{

    protected function repository(): DoctrineSalesActivityRepository
    {
        return $this->em->getRepository(SalesActivity::class);
    }

    //
    public function setInitial(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();
        
        $task = new SetInitialSalesActivityTask($repository);
        
        $duration = $input->get('duration');
        $payload = new SalesActivityData($this->createLabelData($input), $duration);
        
        $user->executeTaskInCompany($task, $payload);
        return $repository->fetchInitialSalesActivityDetail();
    }
    
    public function add(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();
        
        $task = new AddSalesActivityTask($repository);
        
        $duration = $input->get('duration');
        $payload = new SalesActivityData($this->createLabelData($input), $duration);
        
        $user->executeTaskInCompany($task, $payload);
        return $repository->fetchOneByIdOrDie($payload->id);
    }
    
    public function viewList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewSalesActivityList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        
        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
    
    public function viewDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewSalesActivityDetail($this->repository());
        $payload = new ViewDetailPayload($id);
        
        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
