<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CompanyUser;
use Company\Domain\Model\Province;
use Company\Domain\Model\ProvinceData;
use Company\Domain\Task\Province\AddProvince;
use Company\Domain\Task\Province\DisableProvince;
use Company\Domain\Task\Province\EnableProvince;
use Company\Domain\Task\Province\UpdateProvince;
use Company\Domain\Task\Province\ViewAllProvince;
use Company\Domain\Task\Province\ViewProvinceList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineProvinceRepository;
use Resources\Application\InputRequest;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: Province::class)]
class ProvinceController extends BaseController
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
    public function addProvince(CompanyUser $user, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new AddProvince($repository);
        $payload = $this->buildProvinceData($input);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateProvince(CompanyUser $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateProvince($repository);
        $payload = $this->buildProvinceData($input)
                ->setId($id);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function disableProvince(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new DisableProvince($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function enableProvince(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new EnableProvince($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewProvinceList(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewProvinceList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER)]
    public function viewAllProvince(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewAllProvince($this->repository());
        $payload = $this->buildViewAllListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
