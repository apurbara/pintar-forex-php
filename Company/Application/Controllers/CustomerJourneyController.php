<?php

namespace Company\Application\Controllers;

use Company\Domain\Model\CompanyUser;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\CustomerJourneyData;
use Company\Domain\Task\CustomerJourney\AddCustomerJourney;
use Company\Domain\Task\CustomerJourney\DisableCustomerJourney;
use Company\Domain\Task\CustomerJourney\EnableCustomerJourney;
use Company\Domain\Task\CustomerJourney\SetInitialCustomerJourney;
use Company\Domain\Task\CustomerJourney\UpdateCustomerJourney;
use Company\Domain\Task\CustomerJourney\ViewAllActiveCustomerJourney;
use Company\Domain\Task\CustomerJourney\ViewCustomerJourneyDetail;
use Company\Domain\Task\CustomerJourney\ViewCustomerJourneyList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerJourneyRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: CustomerJourney::class)]
class CustomerJourneyController extends BaseController
{

    protected function repository(): DoctrineCustomerJourneyRepository
    {
        return $this->em->getRepository(CustomerJourney::class);
    }
    
    private function createData(InputRequest $input): CustomerJourneyData
    {
        return new CustomerJourneyData($this->createLabelData($input));
    }

    //
    #[Mutation]
    public function setInitialCustomerJourney(CompanyUser $user, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new SetInitialCustomerJourney($repository);
        $payload = $this->createData($input);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->fetchInitialCustomerJourneyDetail();
    }

    #[Mutation]
    public function addCustomerJourney(CompanyUser $user, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new AddCustomerJourney($repository);
        $payload = $this->createData($input);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->aCustomerJourneyDetail($payload->id);
    }

    #[Mutation]
    public function updateCustomerJourney(CompanyUser $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();
        $task = new UpdateCustomerJourney($repository);
        $payload = $this->createData($input)
                ->setId($id);

        $this->executeMutationTaskInCompany($user, $task, $payload);
        return $repository->aCustomerJourneyDetail($payload->id);
    }

    #[Mutation]
    public function disableCustomerJourney(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new DisableCustomerJourney($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->aCustomerJourneyDetail($id);
    }

    #[Mutation]
    public function enableCustomerJourney(CompanyUser $user, string $id)
    {
        $repository = $this->repository();
        $task = new EnableCustomerJourney($repository);

        $this->executeMutationTaskInCompany($user, $task, $id);
        return $repository->aCustomerJourneyDetail($id);
    }

    #[Query]
    public function customerJourneyDetail(CompanyUser $user, string $id)
    {
        $task = new ViewCustomerJourneyDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function customerJourneyList(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewCustomerJourneyList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER)]
    public function viewAllActiveCustomerJourney(CompanyUser $user, InputRequest $input)
    {
        $task = new ViewAllActiveCustomerJourney($this->repository());
        $payload = $this->buildViewAllListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
