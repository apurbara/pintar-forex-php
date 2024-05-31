<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\SalesPerformanceMetric;
use Company\Domain\Model\SalesPerformanceMetricData;
use Company\Domain\Task\InCompany\SalesPerformanceMetric\CreateSalesPerformanceMetric;
use Company\Domain\Task\InCompany\SalesPerformanceMetric\DisableSalesPerformanceMetric;
use Company\Domain\Task\InCompany\SalesPerformanceMetric\EnableSalesPerformanceMetric;
use Company\Domain\Task\InCompany\SalesPerformanceMetric\UpdateSalesPerformanceMetric;
use Company\Domain\Task\InCompany\SalesPerformanceMetric\ViewSalesPerformanceMetricDetail;
use Company\Domain\Task\InCompany\SalesPerformanceMetric\ViewSalesPerformanceMetricList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesPerformanceMetricRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: SalesPerformanceMetric::class)]
class SalesPerformanceMetricController extends Controller
{

    private function repository(): DoctrineSalesPerformanceMetricRepository
    {
        return $this->em->getRepository(SalesPerformanceMetric::class);
    }

    private function createData(InputRequest $input): SalesPerformanceMetricData
    {
        $data = (new SalesPerformanceMetricData())
                        ->setDisplaySchema($input->get('displaySchema'))
                        ->setMetricType($input->get('metricType'))
                        ->setName($input->get('name'))
                        ->setRecurrenceCount($input->get('recurrenceCount'))
                        ->setRecurrenceType($input->get('recurrenceType'));
        foreach ($input->get('evaluations') ?? [] as $evaluationRequest) {
            $evaluationData = (new SalesPerformanceMetric\SalesPerformanceMetricEvaluationData())
                    ->setAlias($evaluationRequest['alias'])
                    ->setEvaluationType($evaluationRequest['evaluationType']);
            $data->addEvaluationData($evaluationData);
        }
        return $data;
    }

    //
    #[Mutation]
    public function createSalesPerformanceMetric(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new CreateSalesPerformanceMetric($repository);
        $payload = $this->createData($input);

        $user->executeTaskInCompany($task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateSalesPerformanceMetric(CompanyUserRoleInterface $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateSalesPerformanceMetric($repository);
        $payload = $this->createData($input)
                ->setId($id);

        $user->executeTaskInCompany($task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function disableSalesPerformanceMetric(CompanyUserRoleInterface $user, string $id)
    {
        $repository = $this->repository();
        $task = new DisableSalesPerformanceMetric($repository);

        $user->executeTaskInCompany($task, $id);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function enableSalesPerformanceMetric(CompanyUserRoleInterface $user, string $id)
    {
        $repository = $this->repository();
        $task = new EnableSalesPerformanceMetric($repository);

        $user->executeTaskInCompany($task, $id);
        return $repository->queryOneById($id);
    }

    #[Query]
    public function viewSalesPerformanceMetricDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewSalesPerformanceMetricDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewSalesPerformanceMetricList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewSalesPerformanceMetricList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
