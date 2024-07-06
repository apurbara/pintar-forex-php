<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CompanyUser;
use Company\Domain\Model\SalesRank;
use Company\Domain\Model\SalesRankData;
use Company\Domain\Task\SalesRank\CreateSalesRank;
use Company\Domain\Task\SalesRank\DisableSalesRank;
use Company\Domain\Task\SalesRank\EnableSalesRank;
use Company\Domain\Task\SalesRank\UpdateSalesRank;
use Company\Domain\Task\SalesRank\ViewSalesRankDetail;
use Company\Domain\Task\SalesRank\ViewSalesRankList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRankRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: SalesRank::class)]
class SalesRankController extends BaseController
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
    public function createSalesRank(CompanyUser $user, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new CreateSalesRank($repository);
        $payload = $this->createData($input);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateSalesRank(CompanyUser $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateSalesRank($repository);
        $payload = $this->createData($input)
                ->setId($id);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function disableSalesRank(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new DisableSalesRank($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function enableSalesRank(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new EnableSalesRank($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Query]
    public function viewSalesRankDetail(CompanyUser $user, string $id)
    {
        $task = new ViewSalesRankDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewSalesRankList(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewSalesRankList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
