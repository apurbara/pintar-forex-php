<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CompanyUser;
use Company\Domain\Model\SalesPerformanceMetric;
use Company\Domain\Model\SalesPerformanceMetric\SalesPerformanceMetricEvaluationData;
use Company\Domain\Model\SalesPerformanceMetricData;
use Company\Domain\Task\SalesPerformanceMetric\CreateSalesPerformanceMetric;
use Company\Domain\Task\SalesPerformanceMetric\DisableSalesPerformanceMetric;
use Company\Domain\Task\SalesPerformanceMetric\EnableSalesPerformanceMetric;
use Company\Domain\Task\SalesPerformanceMetric\UpdateSalesPerformanceMetric;
use Company\Domain\Task\SalesPerformanceMetric\ViewSalesPerformanceMetricDetail;
use Company\Domain\Task\SalesPerformanceMetric\ViewSalesPerformanceMetricList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesPerformanceMetricRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: SalesPerformanceMetric::class)]
class SalesPerformanceMetricController extends BaseController
{

    private function repository(): DoctrineSalesPerformanceMetricRepository
    {
        return $this->em->getRepository(SalesPerformanceMetric::class);
    }

    private function createData(InputRequest $input): SalesPerformanceMetricData
    {
        $data = (new SalesPerformanceMetricData())
                        ->setDisplaySchema($input->get('displaySchema'))
                        ->setSalesPerformanceMetricType($input->get('salesPerformanceMetricType'))
                        ->setName($input->get('name'))
                        ->setRecurrenceCount($input->get('recurrenceCount'))
                        ->setRecurrenceType($input->get('recurrenceType'));
        foreach ($input->get('evaluations') ?? [] as $evaluationRequest) {
            $evaluationData = (new SalesPerformanceMetricEvaluationData())
                    ->setAlias($evaluationRequest['alias'])
                    ->setEvaluationType($evaluationRequest['evaluationType']);
            $data->addEvaluationData($evaluationData);
        }
        return $data;
    }

    //
    #[Mutation]
    public function createSalesPerformanceMetric(CompanyUser $user, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new CreateSalesPerformanceMetric($repository);
        $payload = $this->createData($input);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateSalesPerformanceMetric(CompanyUser $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateSalesPerformanceMetric($repository);
        $payload = $this->createData($input)
                ->setId($id);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function disableSalesPerformanceMetric(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new DisableSalesPerformanceMetric($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function enableSalesPerformanceMetric(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new EnableSalesPerformanceMetric($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Query]
    public function viewSalesPerformanceMetricDetail(CompanyUser $user, string $id)
    {
        $task = new ViewSalesPerformanceMetricDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewSalesPerformanceMetricList(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewSalesPerformanceMetricList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
