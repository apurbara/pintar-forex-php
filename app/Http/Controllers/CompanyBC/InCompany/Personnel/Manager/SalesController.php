<?php

namespace App\Http\Controllers\CompanyBC\InCompany\Personnel\Manager;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use App\Http\Controllers\InputRequest;
use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager;
use Company\Domain\Model\Personnel\Manager\Sales;
use Company\Domain\Model\Personnel\Manager\SalesData;
use Company\Domain\Task\InCompany\Personnel\Manager\Sales\AssignSalesTask;
use Company\Domain\Task\InCompany\Personnel\Manager\Sales\ViewSalesDetailTask;
use Company\Domain\Task\InCompany\Personnel\Manager\Sales\ViewSalesListTask;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class SalesController extends Controller
{
    protected function repository(): DoctrineSalesRepository
    {
        return $this->em->getRepository(Sales::class);
    }
    
    //
    public function assign(CompanyUserRoleInterface $user, string $managerId, InputRequest $input)
    {
        $repository = $this->repository();
        $managerRepository = $this->em->getRepository(Manager::class);
        $personnelRepository = $this->em->getRepository(Personnel::class);
        $areaRepository = $this->em->getRepository(Area::class);
        
        $task = new AssignSalesTask($repository, $managerRepository, $personnelRepository, $areaRepository);
        
        $type = $input->get('type');
        $payload = (new SalesData($type))
                ->setManagerId($managerId)
                ->setPersonnelId($input->get('personnelId'))
                ->setAreaId($input->get('areaId'));
        
        $user->executeTaskInCompany($task, $payload);
        //
        return $this->repository()->fetchOneByIdOrDie($payload->id);
    }
    
    public function viewList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewSalesListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeTaskInCompany($task, $payload);
        
        return $payload->result;
    }
    
    public function viewDetail(CompanyUserRoleInterface $user, string $salesId)
    {
        $task = new ViewSalesDetailTask($this->repository());
        $payload = new ViewDetailPayload($salesId);
        $user->executeTaskInCompany($task, $payload);
        
        return $payload->result;
    }
}
