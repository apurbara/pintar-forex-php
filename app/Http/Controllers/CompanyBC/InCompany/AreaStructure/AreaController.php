<?php

namespace App\Http\Controllers\CompanyBC\InCompany\AreaStructure;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use App\Http\Controllers\InputRequest;
use Company\Domain\Model\AreaStructure;
use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Task\InCompany\AreaStructure\Area\AddChildAreaTask;
use Company\Domain\Task\InCompany\AreaStructure\Area\AddRootAreaTask;
use Company\Domain\Task\InCompany\AreaStructure\Area\ViewAreaDetail;
use Company\Domain\Task\InCompany\AreaStructure\Area\ViewAreaListTask;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineAreaRepository;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class AreaController extends Controller
{

    protected function areaRepository(): DoctrineAreaRepository
    {
        return $this->em->getRepository(Area::class);
    }
    protected function areaStructureRepository()
    {
        return $this->em->getRepository(AreaStructure::class);
    }

    //
    public function addRoot(CompanyUserRoleInterface $user, string $areaStructureId, InputRequest $input)
    {
        $repository = $this->areaRepository();
        $task = new AddRootAreaTask($repository, $this->areaStructureRepository());
        $payload = (new AreaStructure\AreaData($this->createLabelData($input)))
                ->setAreaStructureId($areaStructureId);
        $user->executeTaskInCompany($task, $payload);
        
        return $repository->fetchOneByIdOrDie($payload->id);
    }
    
    public function addChild(CompanyUserRoleInterface $user, string $parentAreaId, InputRequest $input)
    {
        $repository = $this->areaRepository();
        $task = new AddChildAreaTask($repository, $this->areaStructureRepository());
        $payload = (new AreaStructure\AreaData($this->createLabelData($input)))
                ->setParentAreaId($parentAreaId)
                ->setAreaStructureId($input->get('areaStructureId'));
        $user->executeTaskInCompany($task, $payload);
        
        return $repository->fetchOneByIdOrDie($payload->id);
    }
    
    public function viewList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewAreaListTask($this->areaRepository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeTaskInCompany($task, $payload);
        
        return $payload->result;
    }
    
    public function viewDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewAreaDetail($this->areaRepository());
        $payload = new ViewDetailPayload($id);
        $user->executeTaskInCompany($task, $payload);
        
        return $payload->result;
    }
}
