<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\Province;
use Company\Domain\Model\Province\City;
use Company\Domain\Model\Province\CityData;
use Company\Domain\Task\InCompany\City\AddCity;
use Company\Domain\Task\InCompany\City\DisableCity;
use Company\Domain\Task\InCompany\City\EnableCity;
use Company\Domain\Task\InCompany\City\UpdateCity;
use Company\Domain\Task\InCompany\City\ViewAllCity;
use Company\Domain\Task\InCompany\City\ViewCityList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCityRepository;
use Resources\Application\InputRequest;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: City::class)]
class CityController extends Controller
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
    public function addCity(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();
        $provinceRepository = $this->em->getRepository(Province::class);
        $task = new AddCity($repository, $provinceRepository);
        $payload = $this->buildCityData($input)
                ->setProvinceId($input->get('Province_id'));

        $user->executeTaskInCompany($task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateCity(CompanyUserRoleInterface $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $provinceRepository = $this->em->getRepository(Province::class);
        $task = new UpdateCity($repository, $provinceRepository);
        $payload = $this->buildCityData($input)
                ->setId($id)
                ->setProvinceId($input->get('Province_id'));

        $user->executeTaskInCompany($task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function disableCity(CompanyUserRoleInterface $user, string $id)
    {
        $repository = $this->repository();
        $task = new DisableCity($repository);

        $user->executeTaskInCompany($task, $id);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function enableCity(CompanyUserRoleInterface $user, string $id)
    {
        $repository = $this->repository();
        $task = new EnableCity($repository);

        $user->executeTaskInCompany($task, $id);
        return $repository->queryOneById($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewCityList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewCityList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER)]
    public function viewAllCity(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewAllCity($this->repository());
        $payload = $this->buildViewAllListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
