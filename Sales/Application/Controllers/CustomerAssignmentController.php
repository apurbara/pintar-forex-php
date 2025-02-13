<?php

namespace Sales\Application\Controllers;

use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Domain\TaskPayload\ViewSummaryPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Domain\DependencyModel\CustomerData;
use Sales\Domain\DependencyModel\CustomerJourney;
use Sales\Domain\DependencyModel\Province\City;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignmentData;
use Sales\Domain\Task\CustomerAssignment\UpdateCustomer;
use Sales\Domain\Task\CustomerAssignment\UpdateCustomerPayload;
use Sales\Domain\Task\CustomerAssignment\UpdateJourney;
use Sales\Domain\Task\CustomerAssignment\ViewCustomerAssignmentDetail;
use Sales\Domain\Task\CustomerAssignment\ViewCustomerAssignmentList;
use Sales\Domain\Task\CustomerAssignment\ViewTotalCustomerAssignment;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerAssignmentRepository;

#[GraphqlMapableController(entity: CustomerAssignment::class)]
class CustomerAssignmentController extends BaseController
{

    protected function repository(): DoctrineCustomerAssignmentRepository
    {
        return $this->em->getRepository(CustomerAssignment::class);
    }

    #[Mutation]
    public function updateCustomerAssignmentJourney(Sales $sales, InputRequest $input)
    {
        $repository = $this->repository();
        $customerJourneyRepository = $this->em->getRepository(CustomerJourney::class);
        $task = new UpdateJourney($repository, $customerJourneyRepository);
        $payload = (new CustomerAssignmentData())
                ->setId($input->get('id'))
                ->setCustomerJourneyId($input->get('CustomerJourney_id'));

        $this->executeSalesMutationTask($sales, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateCustomerBio(Sales $sales, InputRequest $input)
    {
        $repository = $this->repository();
        $cityRepository = $this->em->getRepository(City::class);
        $task = new UpdateCustomer($repository, $cityRepository);

        $customerInput = $input->get('customer');
        $customerData = (new CustomerData())
                ->setCityId($customerInput['City_id'] ?? null)
                ->setName($customerInput['name'])
                ->setEmail($customerInput['email'] ?? null);
        $payload = (new UpdateCustomerPayload())
                ->setId($input->get('id'))
                ->setCustomerData($customerData);

        $this->executeSalesMutationTask($sales, $task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function customerAssignmentList(Sales $sales, InputRequest $input)
    {
        $task = new ViewCustomerAssignmentList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }

    #[Query]
    public function customerAssignmentDetail(Sales $sales, string $id)
    {
        $task = new ViewCustomerAssignmentDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }

    public function totalCustomerAssignment(Sales $sales, InputRequest $input)
    {
        $task = new ViewTotalCustomerAssignment($this->repository());
        $searchSchema = [
            'filters' => $input->get('filters'),
        ];
        $payload = new ViewSummaryPayload($searchSchema);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }
}
