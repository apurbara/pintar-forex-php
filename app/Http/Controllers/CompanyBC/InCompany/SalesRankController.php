<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\SalesRank;
use Company\Domain\Model\SalesRankData;
use Company\Domain\Task\InCompany\SalesRank\CreateSalesRank;
use Company\Domain\Task\InCompany\SalesRank\DisableSalesRank;
use Company\Domain\Task\InCompany\SalesRank\EnableSalesRank;
use Company\Domain\Task\InCompany\SalesRank\UpdateSalesRank;
use Company\Domain\Task\InCompany\SalesRank\ViewSalesRankDetail;
use Company\Domain\Task\InCompany\SalesRank\ViewSalesRankList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRankRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: SalesRank::class)]
class SalesRankController extends Controller
{

    private function repository(): DoctrineSalesRankRepository
    {
        return $this->em->getRepository(SalesRank::class);
    }

    private function createData(InputRequest $input): SalesRankData
    {
        return (new SalesRankData())
                        ->setDisplaySalesNumber($input->get('displaySalesNumber'))
                        ->setDisplaySchema($input->get('displaySchema'))
                        ->setEvaluationType($input->get('evaluationType'))
                        ->setMetricType($input->get('metricType'))
                        ->setName($input->get('name'))
                        ->setOrder($input->get('queryOrder'))
                        ->setRecurrenceType($input->get('recurrenceType'));
    }

    //
    #[Mutation]
    public function createSalesRank(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new CreateSalesRank($repository);
        $payload = $this->createData($input);

        $user->executeTaskInCompany($task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateSalesRank(CompanyUserRoleInterface $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateSalesRank($repository);
        $payload = $this->createData($input)
                ->setId($id);

        $user->executeTaskInCompany($task, $payload);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function disableSalesRank(CompanyUserRoleInterface $user, string $id)
    {
        $repository = $this->repository();
        $task = new DisableSalesRank($repository);

        $user->executeTaskInCompany($task, $id);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function enableSalesRank(CompanyUserRoleInterface $user, string $id)
    {
        $repository = $this->repository();
        $task = new EnableSalesRank($repository);

        $user->executeTaskInCompany($task, $id);
        return $repository->queryOneById($id);
    }

    #[Query]
    public function viewSalesRankDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewSalesRankDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewSalesRankList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewSalesRankList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
