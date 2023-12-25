<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\AreaStructure;
use Company\Domain\Model\AreaStructureData;
use Company\Domain\Task\InCompany\AreaStructure\AddChildAreaStructureTask;
use Company\Domain\Task\InCompany\AreaStructure\AddRootAreaStructureTask;
use Company\Domain\Task\InCompany\AreaStructure\ViewAreaStructureDetailTask;
use Company\Domain\Task\InCompany\AreaStructure\ViewAreaStructureListTask;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineAreaStructureRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: AreaStructure::class)]
class AreaStructureController extends Controller
{

    protected function areaStructureRepository(): DoctrineAreaStructureRepository
    {
        return $this->em->getRepository(AreaStructure::class);
    }

    //
    #[Mutation]
    public function addRootAreaStructure(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $repository = $this->areaStructureRepository();
        $task = new AddRootAreaStructureTask($repository);
        $payload = new AreaStructureData($this->createLabelData($input));
        $user->executeTaskInCompany($task, $payload);

        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function addChildAreaStructure(CompanyUserRoleInterface $user, ?string $parentAreaStructureId, InputRequest $input)
    {
        $repository = $this->areaStructureRepository();
        $task = new AddChildAreaStructureTask($repository);
        $payload = (new AreaStructureData($this->createLabelData($input)))
                ->setParentId($parentAreaStructureId ?? $input->get('AreaStructure_idOfParent'));
        $user->executeTaskInCompany($task, $payload);

        return $repository->queryOneById($payload->id);
    }
    
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function areaStructureList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewAreaStructureListTask($this->areaStructureRepository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeTaskInCompany($task, $payload);
        
        return $payload->result;
    }
    
    #[Query]
    public function areaStructureDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewAreaStructureDetailTask($this->areaStructureRepository());
        $payload = new ViewDetailPayload($id);
        $user->executeTaskInCompany($task, $payload);
        
        return $payload->result;
    }
}
