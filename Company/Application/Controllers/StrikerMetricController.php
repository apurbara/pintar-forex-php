<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CompanyUser;
use Company\Domain\Model\StrikerMetric;
use Company\Domain\Model\StrikerMetricData;
use Company\Domain\Task\StrikerMetric\CreateStrikerMetric;
use Company\Domain\Task\StrikerMetric\DisableStrikerMetric;
use Company\Domain\Task\StrikerMetric\EnableStrikerMetric;
use Company\Domain\Task\StrikerMetric\UpdateStrikerMetric;
use Company\Domain\Task\StrikerMetric\ViewStrikerMetricDetail;
use Company\Domain\Task\StrikerMetric\ViewStrikerMetricList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineStrikerMetricRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: StrikerMetric::class)]
class StrikerMetricController extends BaseController
{

    private function repository(): DoctrineStrikerMetricRepository
    {
        return $this->em->getRepository(StrikerMetric::class);
    }

    //
    private function buildStrikerMetricData(InputRequest $input): StrikerMetricData
    {
        return (new StrikerMetricData())
                        ->setDailyReminderTarget($input->get('dailyReminderTarget'))
                        ->setEvaluationType($input->get('evaluationType'))
                        ->setName($input->get('name'))
                        ->setTarget($input->get('target'))
                        ->setRecurrenceCount($input->get('recurrenceCount'))
                        ->setRecurrenceType($input->get('recurrenceType'))
                        ->setSalesMetricType($input->get('salesMetricType'));
    }

    #[Mutation]
    public function createStrikerMetric(CompanyUser $user, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new CreateStrikerMetric($repository);
        $payload = $this->buildStrikerMetricData($input);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateStrikerMetric(CompanyUser $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateStrikerMetric($repository);
        $payload = $this->buildStrikerMetricData($input)
                ->setId($id);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function disableStrikerMetric(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new DisableStrikerMetric($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function enableStrikerMetric(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new EnableStrikerMetric($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->queryOneById($id);
    }

    #[Query]
    public function viewStrikerMetricDetail(CompanyUser $user, string $id)
    {
        $task = new ViewStrikerMetricDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewStrikerMetricList(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewStrikerMetricList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
