<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use App\Http\Controllers\InputRequest;
use Company\Domain\Model\AreaStructure;
use Company\Domain\Model\AreaStructureData;
use Company\Domain\Task\InCompany\AreaStructure\AddChildAreaStructureTask;
use Company\Domain\Task\InCompany\AreaStructure\AddRootAreaStructureTask;
use Company\Domain\Task\InCompany\AreaStructure\ViewAreaStructureDetailTask;
use Company\Domain\Task\InCompany\AreaStructure\ViewAreaStructureListTask;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineAreaStructureRepository;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class AreaStructureController extends Controller
{

    protected function areaStructureRepository(): DoctrineAreaStructureRepository
    {
        return $this->em->getRepository(AreaStructure::class);
    }

    //
    public function addRoot(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $repository = $this->areaStructureRepository();
        $task = new AddRootAreaStructureTask($repository);
        $payload = new AreaStructureData($this->createLabelData($input));
        $user->executeTaskInCompany($task, $payload);

        return $repository->fetchOneByIdOrDie($payload->id);
    }

    public function addChild(CompanyUserRoleInterface $user, string $parentAreaStructureId, InputRequest $input)
    {
        $repository = $this->areaStructureRepository();
        $task = new AddChildAreaStructureTask($repository);
        $payload = (new AreaStructureData($this->createLabelData($input)))
                ->setParentId($parentAreaStructureId);
        $user->executeTaskInCompany($task, $payload);

        return $repository->fetchOneByIdOrDie($payload->id);
    }
    
    public function viewList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewAreaStructureListTask($this->areaStructureRepository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeTaskInCompany($task, $payload);
        
        return $payload->result;
    }
    
    public function viewDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewAreaStructureDetailTask($this->areaStructureRepository());
        $payload = new ViewDetailPayload($id);
        $user->executeTaskInCompany($task, $payload);
        
        return $payload->result;
    }
}
