<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\CompanyMetric;
use Company\Domain\Model\CompanyMetricData;
use Company\Domain\Task\InCompany\CompanyMetric\CreateCompanyMetric;
use Company\Domain\Task\InCompany\CompanyMetric\DisableCompanyMetric;
use Company\Domain\Task\InCompany\CompanyMetric\EnableCompanyMetric;
use Company\Domain\Task\InCompany\CompanyMetric\UpdateCompanyMetric;
use Company\Domain\Task\InCompany\CompanyMetric\ViewCompanyMetricDetail;
use Company\Domain\Task\InCompany\CompanyMetric\ViewCompanyMetricList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCompanyMetricRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: CompanyMetric::class)]
class CompanyMetricController extends Controller
{

    private function repository(): DoctrineCompanyMetricRepository
    {
        return $this->em->getRepository(CompanyMetric::class);
    }

    private function createData(InputRequest $input): CompanyMetricData
    {
        return (new CompanyMetricData())
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
    public function createCompanyMetric(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new CreateCompanyMetric($repository);
        $payload = $this->createData($input);

        $user->executeTaskInCompany($task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateCompanyMetric(CompanyUserRoleInterface $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateCompanyMetric($repository);
        $payload = $this->createData($input)
                ->setId($id);

        $user->executeTaskInCompany($task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function disableCompanyMetric(CompanyUserRoleInterface $user, string $id)
    {
        $repository = $this->repository();
        $task = new DisableCompanyMetric($repository);

        $user->executeTaskInCompany($task, $id);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function enableCompanyMetric(CompanyUserRoleInterface $user, string $id)
    {
        $repository = $this->repository();
        $task = new EnableCompanyMetric($repository);

        $user->executeTaskInCompany($task, $id);
        return $repository->queryOneById($id);
    }

    #[Query]
    public function viewCompanyMetricDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewCompanyMetricDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewCompanyMetricList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewCompanyMetricList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
