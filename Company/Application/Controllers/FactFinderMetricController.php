<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CompanyUser;
use Company\Domain\Model\FactFinderMetric;
use Company\Domain\Model\FactFinderMetricData;
use Company\Domain\Task\FactFinderMetric\CreateFactFinderMetric;
use Company\Domain\Task\FactFinderMetric\DisableFactFinderMetric;
use Company\Domain\Task\FactFinderMetric\EnableFactFinderMetric;
use Company\Domain\Task\FactFinderMetric\UpdateFactFinderMetric;
use Company\Domain\Task\FactFinderMetric\ViewFactFinderMetricDetail;
use Company\Domain\Task\FactFinderMetric\ViewFactFinderMetricList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineFactFinderMetricRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: FactFinderMetric::class)]
class FactFinderMetricController extends BaseController
{

    private function repository(): DoctrineFactFinderMetricRepository
    {
        return $this->em->getRepository(FactFinderMetric::class);
    }

    //
    private function buildFactFinderMetricData(InputRequest $input): FactFinderMetricData
    {
        return (new FactFinderMetricData())
                        ->setDailyReminderTarget($input->get('dailyReminderTarget'))
                        ->setEvaluationType($input->get('evaluationType'))
                        ->setName($input->get('name'))
                        ->setTarget($input->get('target'))
                        ->setRecurrenceCount($input->get('recurrenceCount'))
                        ->setRecurrenceType($input->get('recurrenceType'))
                        ->setSalesMetricType($input->get('salesMetricType'));
    }

    #[Mutation]
    public function createFactFinderMetric(CompanyUser $user, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new CreateFactFinderMetric($repository);
        $payload = $this->buildFactFinderMetricData($input);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateFactFinderMetric(CompanyUser $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateFactFinderMetric($repository);
        $payload = $this->buildFactFinderMetricData($input)
                ->setId($id);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function disableFactFinderMetric(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new DisableFactFinderMetric($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function enableFactFinderMetric(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new EnableFactFinderMetric($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Query]
    public function viewFactFinderMetricDetail(CompanyUser $user, string $id)
    {
        $task = new ViewFactFinderMetricDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewFactFinderMetricList(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewFactFinderMetricList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
