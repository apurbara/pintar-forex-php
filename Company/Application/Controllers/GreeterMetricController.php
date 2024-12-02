<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CompanyUser;
use Company\Domain\Model\GreeterMetric;
use Company\Domain\Model\GreeterMetricData;
use Company\Domain\Task\GreeterMetric\CreateGreeterMetric;
use Company\Domain\Task\GreeterMetric\DisableGreeterMetric;
use Company\Domain\Task\GreeterMetric\EnableGreeterMetric;
use Company\Domain\Task\GreeterMetric\UpdateGreeterMetric;
use Company\Domain\Task\GreeterMetric\ViewGreeterMetricDetail;
use Company\Domain\Task\GreeterMetric\ViewGreeterMetricList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineGreeterMetricRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: GreeterMetric::class)]
class GreeterMetricController extends BaseController
{

    private function repository(): DoctrineGreeterMetricRepository
    {
        return $this->em->getRepository(GreeterMetric::class);
    }

    //
    private function buildGreeterMetricData(InputRequest $input): GreeterMetricData
    {
        return (new GreeterMetricData())
                        ->setDailyReminderTarget($input->get('dailyReminderTarget'))
                        ->setEvaluationType($input->get('evaluationType'))
                        ->setMonthlyTarget($input->get('monthlyTarget'))
                        ->setRecurrenceCount($input->get('recurrenceCount'))
                        ->setRecurrenceType($input->get('recurrenceType'))
                        ->setSalesMetricType($input->get('salesMetricType'));
    }

    #[Mutation]
    public function createGreeterMetric(CompanyUser $user, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new CreateGreeterMetric($repository);
        $payload = $this->buildGreeterMetricData($input);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateGreeterMetric(CompanyUser $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateGreeterMetric($repository);
        $payload = $this->buildGreeterMetricData($input)
                ->setId($id);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function disableGreeterMetric(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new DisableGreeterMetric($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function enableGreeterMetric(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new EnableGreeterMetric($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Query]
    public function viewGreeterMetricDetail(CompanyUser $user, string $id)
    {
        $task = new ViewGreeterMetricDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewGreeterMetricList(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewGreeterMetricList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
