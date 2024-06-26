<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\Province;
use Company\Domain\Model\ProvinceData;
use Company\Domain\Task\InCompany\Province\AddProvince;
use Company\Domain\Task\InCompany\Province\DisableProvince;
use Company\Domain\Task\InCompany\Province\EnableProvince;
use Company\Domain\Task\InCompany\Province\UpdateProvince;
use Company\Domain\Task\InCompany\Province\ViewAllProvince;
use Company\Domain\Task\InCompany\Province\ViewProvinceList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineProvinceRepository;
use Resources\Application\InputRequest;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: Province::class)]
class ProvinceController extends Controller
{

    protected function repository(): DoctrineProvinceRepository
    {
        return $this->em->getRepository(Province::class);
    }

    protected function buildProvinceData(InputRequest $input): ProvinceData
    {
        return (new ProvinceData())
                        ->setName($input->get('name'));
    }

    //
    #[Mutation]
    public function addProvince(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new AddProvince($repository);
        $payload = $this->buildProvinceData($input);

        $user->executeTaskInCompany($task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateProvince(CompanyUserRoleInterface $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateProvince($repository);
        $payload = $this->buildProvinceData($input)
                ->setId($id);

        $user->executeTaskInCompany($task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function disableProvince(CompanyUserRoleInterface $user, string $id)
    {
        $repository = $this->repository();
        $task = new DisableProvince($repository);

        $user->executeTaskInCompany($task, $id);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function enableProvince(CompanyUserRoleInterface $user, string $id)
    {
        $repository = $this->repository();
        $task = new EnableProvince($repository);

        $user->executeTaskInCompany($task, $id);
        return $repository->queryOneById($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewProvinceList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewProvinceList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER)]
    public function viewAllProvince(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewAllProvince($this->repository());
        $payload = $this->buildViewAllListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
