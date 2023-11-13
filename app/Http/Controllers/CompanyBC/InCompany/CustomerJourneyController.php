<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use App\Http\Controllers\InputRequest;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\CustomerJourneyData;
use Company\Domain\Task\InCompany\CustomerJourney\AddCustomerJourney;
use Company\Domain\Task\InCompany\CustomerJourney\SetInitialCustomerJourney;
use Company\Domain\Task\InCompany\CustomerJourney\ViewCustomerJourneyDetail;
use Company\Domain\Task\InCompany\CustomerJourney\ViewCustomerJourneyList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerJourneyRepository;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class CustomerJourneyController extends Controller
{

    protected function repository(): DoctrineCustomerJourneyRepository
    {
        return $this->em->getRepository(CustomerJourney::class);
    }

    //
    public function setInitial(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new SetInitialCustomerJourney($repository);
        $payload = new CustomerJourneyData($this->createLabelData($input));
        
        $user->executeTaskInCompany($task, $payload);
        
        return $repository->fetchInitialCustomerJourneyDetail();
    }
    
    public function add(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new AddCustomerJourney($repository);
        $payload = new CustomerJourneyData($this->createLabelData($input));
        
        $user->executeTaskInCompany($task, $payload);
        
        return $repository->aCustomerJourneyDetail($payload->id);
    }
    
    public function viewDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewCustomerJourneyDetail($this->repository());
        $payload = new ViewDetailPayload($id);
        
        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
    
    public function viewList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewCustomerJourneyList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        
        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
    
}
