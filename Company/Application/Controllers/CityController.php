<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CompanyUser;
use Company\Domain\Model\Province;
use Company\Domain\Model\Province\City;
use Company\Domain\Model\Province\CityData;
use Company\Domain\Task\City\AddCity;
use Company\Domain\Task\City\DisableCity;
use Company\Domain\Task\City\EnableCity;
use Company\Domain\Task\City\UpdateCity;
use Company\Domain\Task\City\ViewAllCity;
use Company\Domain\Task\City\ViewCityDetail;
use Company\Domain\Task\City\ViewCityList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCityRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: City::class)]
class CityController extends BaseController
{

    protected function repository(): DoctrineCityRepository
    {
        return $this->em->getRepository(City::class);
    }

    protected function buildCityData(InputRequest $input): CityData
    {
        return (new CityData())
                        ->setName($input->get('name'));
    }

    //
    #[Mutation]
    public function addCity(CompanyUser $user, InputRequest $input)
    {
        $repository = $this->repository();
        $provinceRepository = $this->em->getRepository(Province::class);
        $task = new AddCity($repository, $provinceRepository);
        $payload = $this->buildCityData($input)
                ->setProvinceId($input->get('Province_id'));

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateCity(CompanyUser $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $provinceRepository = $this->em->getRepository(Province::class);
        $task = new UpdateCity($repository, $provinceRepository);
        $payload = $this->buildCityData($input)
                ->setId($id)
                ->setProvinceId($input->get('Province_id'));

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function disableCity(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new DisableCity($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function enableCity(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new EnableCity($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewCityList(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewCityList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER)]
    public function viewAllCity(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewAllCity($this->repository());
        $payload = $this->buildViewAllListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query]
    public function viewCityDetail(CompanyUser $user, string $id)
    {
        $task = new ViewCityDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
