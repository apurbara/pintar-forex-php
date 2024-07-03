<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CompanyMetric;
use Company\Domain\Model\CompanyMetricData;
use Company\Domain\Model\CompanyUser;
use Company\Domain\Task\CompanyMetric\CreateCompanyMetric;
use Company\Domain\Task\CompanyMetric\DisableCompanyMetric;
use Company\Domain\Task\CompanyMetric\EnableCompanyMetric;
use Company\Domain\Task\CompanyMetric\UpdateCompanyMetric;
use Company\Domain\Task\CompanyMetric\ViewCompanyMetricDetail;
use Company\Domain\Task\CompanyMetric\ViewCompanyMetricList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCompanyMetricRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: CompanyMetric::class)]
class CompanyMetricController extends BaseController
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
    public function createCompanyMetric(CompanyUser $user, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new CreateCompanyMetric($repository);
        $payload = $this->createData($input);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateCompanyMetric(CompanyUser $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateCompanyMetric($repository);
        $payload = $this->createData($input)
                ->setId($id);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function disableCompanyMetric(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new DisableCompanyMetric($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function enableCompanyMetric(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new EnableCompanyMetric($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Query]
    public function viewCompanyMetricDetail(CompanyUser $user, string $id)
    {
        $task = new ViewCompanyMetricDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewCompanyMetricList(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewCompanyMetricList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
