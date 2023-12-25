<?php

namespace App\Http\Controllers\CompanyBC\InCompany\AreaStructure;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\AreaStructure;
use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Task\InCompany\AreaStructure\Area\AddChildAreaTask;
use Company\Domain\Task\InCompany\AreaStructure\Area\AddRootAreaTask;
use Company\Domain\Task\InCompany\AreaStructure\Area\ViewAreaDetail;
use Company\Domain\Task\InCompany\AreaStructure\Area\ViewAreaListTask;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineAreaRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: Area::class)]
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
    #[Mutation]
    public function addRootArea(CompanyUserRoleInterface $user, ?string $areaStructureId, InputRequest $input)
    {
        $repository = $this->areaRepository();
        $task = new AddRootAreaTask($repository, $this->areaStructureRepository());
        $payload = (new AreaStructure\AreaData($this->createLabelData($input)))
                ->setAreaStructureId($areaStructureId ?? $input->get('AreaStructure_id'));
        $user->executeTaskInCompany($task, $payload);

        return $repository->fetchOneByIdOrDie($payload->id);
    }

    #[Mutation]
    public function addChildArea(CompanyUserRoleInterface $user, ?string $parentAreaId, InputRequest $input)
    {
        $repository = $this->areaRepository();
        $task = new AddChildAreaTask($repository, $this->areaStructureRepository());
        $payload = (new AreaStructure\AreaData($this->createLabelData($input)))
                ->setParentAreaId($parentAreaId ?? $input->get('Area_idOfParent'))
                ->setAreaStructureId($input->get('AreaStructure_id'));
        $user->executeTaskInCompany($task, $payload);

        return $repository->fetchOneByIdOrDie($payload->id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function areaList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewAreaListTask($this->areaRepository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeTaskInCompany($task, $payload);

        return $payload->result;
    }

    #[Query]
    public function areaDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewAreaDetail($this->areaRepository());
        $payload = new ViewDetailPayload($id);
        $user->executeTaskInCompany($task, $payload);

        return $payload->result;
    }
}
