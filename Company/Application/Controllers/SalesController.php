<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CompanyUser;
use Company\Domain\Model\Manager;
use Company\Domain\Model\Manager\Sales;
use Company\Domain\Model\Manager\SalesData;
use Company\Domain\Model\Province\City;
use Company\Domain\Task\Sales\AddSales;
use Company\Domain\Task\Sales\TerminateSalesContract;
use Company\Domain\Task\Sales\UpdateSales;
use Company\Domain\Task\Sales\ViewAllSales;
use Company\Domain\Task\Sales\ViewSalesDetail;
use Company\Domain\Task\Sales\ViewSalesList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Shared\Domain\ValueObject\AccountInfoData;

#[GraphqlMapableController(entity: Sales::class)]
class SalesController extends BaseController
{

    protected function repository(): DoctrineSalesRepository
    {
        return $this->em->getRepository(Sales::class);
    }

    private function createSalesData(InputRequest $input): SalesData
    {
        $name = $input->get('name');
        $email = $input->get('email');
        $password = $input->get('password');
        $accountInfoData = new AccountInfoData($name, $email, $password);
        return (new SalesData())
                        ->setAccountInfoData($accountInfoData)
                        ->setRole($input->get('role'));
    }

    //
    #[Mutation]
    public function addSales(CompanyUser $user, InputRequest $input)
    {
        $repository = $this->repository();
        $managerRepository = $this->em->getRepository(Manager::class);
        $cityRepository = $this->em->getRepository(City::class);

        $task = new AddSales($repository, $managerRepository, $cityRepository);
        $payload = $this->createSalesData($input)
                ->setManagerId($input->get('Manager_id'))
                ->setCityId($input->get('City_id'));

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateSales(CompanyUser $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $managerRepository = $this->em->getRepository(Manager::class);
        $cityRepository = $this->em->getRepository(City::class);

        $task = new UpdateSales($repository, $managerRepository, $cityRepository);
        $payload = (new SalesData())
                ->setRole($input->get('role'))
                ->setManagerId($input->get('Manager_id'))
                ->setCityId($input->get('City_id'))
                ->setId($id);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function terminateSalesContract(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new TerminateSalesContract($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $this->repository()->queryOneById($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewSalesList(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewSalesList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query]
    public function viewSalesDetail(CompanyUser $user, string $id)
    {
        $task = new ViewSalesDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER)]
    public function viewAllSales(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewAllSales($this->repository());
        $payload = $this->buildViewAllListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
