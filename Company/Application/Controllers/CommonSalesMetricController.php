<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CommonSalesMetric;
use Company\Domain\Model\CommonSalesMetricData;
use Company\Domain\Model\CompanyUser;
use Company\Domain\Task\CommonSalesMetric\CreateCommonSalesMetric;
use Company\Domain\Task\CommonSalesMetric\DisableCommonSalesMetric;
use Company\Domain\Task\CommonSalesMetric\EnableCommonSalesMetric;
use Company\Domain\Task\CommonSalesMetric\UpdateCommonSalesMetric;
use Company\Domain\Task\CommonSalesMetric\ViewCommonSalesMetricDetail;
use Company\Domain\Task\CommonSalesMetric\ViewCommonSalesMetricList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCommonSalesMetricRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: CommonSalesMetric::class)]
class CommonSalesMetricController extends BaseController
{

    private function repository(): DoctrineCommonSalesMetricRepository
    {
        return $this->em->getRepository(CommonSalesMetric::class);
    }

    private function createData(InputRequest $input): CommonSalesMetricData
    {
        return (new CommonSalesMetricData())
                        ->setDisplaySchema($input->get('displaySchema'))
                        ->setEvaluationType($input->get('evaluationType'))
                        ->setMetricType($input->get('metricType'))
                        ->setName($input->get('name'))
                        ->setRecurrenceCount($input->get('recurrenceCount'))
                        ->setRecurrenceType($input->get('recurrenceType'))
                        ->setTarget($input->get('target'));
    }

    //
    #[Mutation]
    public function createCommonSalesMetric(CompanyUser $user, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new CreateCommonSalesMetric($repository);
        $payload = $this->createData($input);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateCommonSalesMetric(CompanyUser $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateCommonSalesMetric($repository);
        $payload = $this->createData($input)
                ->setId($id);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function disableCommonSalesMetric(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new DisableCommonSalesMetric($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function enableCommonSalesMetric(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new EnableCommonSalesMetric($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Query]
    public function viewCommonSalesMetricDetail(CompanyUser $user, string $id)
    {
        $task = new ViewCommonSalesMetricDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewCommonSalesMetricList(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewCommonSalesMetricList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
