<?php

namespace App\Http\Controllers\SalesBC\BySales;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SalesBC\SalesRole;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Domain\TaskPayload\ViewSummaryPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Domain\DependencyModel\AreaStructure\Area;
use Sales\Domain\DependencyModel\AreaStructure\Area\CustomerData;
use Sales\Domain\DependencyModel\CustomerJourney;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignmentData;
use Sales\Domain\Task\BySales\CustomerAssignment\UpdateCustomer;
use Sales\Domain\Task\BySales\CustomerAssignment\UpdateCustomerPayload;
use Sales\Domain\Task\BySales\CustomerAssignment\UpdateJourney;
use Sales\Domain\Task\BySales\CustomerAssignment\ViewCustomerAssignmentDetail;
use Sales\Domain\Task\BySales\CustomerAssignment\ViewCustomerAssignmentList;
use Sales\Domain\Task\BySales\CustomerAssignment\ViewTotalCustomerAssignment;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerAssignmentRepository;

#[GraphqlMapableController(entity: CustomerAssignment::class)]
class CustomerAssignmentController extends Controller
{

    protected function repository(): DoctrineCustomerAssignmentRepository
    {
        return $this->em->getRepository(CustomerAssignment::class);
    }

    #[Mutation]
    public function updateCustomerAssignmentJourney(SalesRole $user, InputRequest $input)
    {
        $repository = $this->repository();
        $customerJourneyRepository = $this->em->getRepository(CustomerJourney::class);
        $task = new UpdateJourney($repository, $customerJourneyRepository);
        $payload = (new CustomerAssignmentData())
                ->setId($input->get('id'))
                ->setCustomerJourneyId($input->get('CustomerJourney_id'));
        $user->executeSalesTask($task, $payload);

        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateCustomerBio(SalesRole $user, InputRequest $input)
    {
        $repository = $this->repository();
        $customerJourneyRepository = $this->em->getRepository(CustomerJourney::class);
        $areaRepository = $this->em->getRepository(Area::class);
        $task = new UpdateCustomer($repository, $areaRepository);

        $customerInput = $input->get('customer');
        $customerData = (new CustomerData($customerInput['name'] ?? null, $customerInput['email'] ?? null, $customerInput['phone'] ?? null))
                ->setAreaId($customerInput['Area_id'] ?? null)
                ->setSource($customerInput['source'] ?? null);
        $payload = (new UpdateCustomerPayload())
                ->setId($input->get('id'))
                ->setCustomerData($customerData);
        $user->executeSalesTask($task, $payload);

        return $repository->queryOneById($payload->id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function customerAssignmentList(SalesRole $user, InputRequest $input)
    {
        $task = new ViewCustomerAssignmentList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeSalesTask($task, $payload);

        return $payload->result;
    }

    #[Query]
    public function customerAssignmentDetail(SalesRole $user, string $id)
    {
        $task = new ViewCustomerAssignmentDetail($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeSalesTask($task, $payload);

        return $payload->result;
    }

    public function totalCustomerAssignment(SalesRole $user, InputRequest $input)
    {
        $task = new ViewTotalCustomerAssignment($this->repository());
        $searchSchema = [
            'filters' => $input->get('filters'),
        ];
        $payload = new ViewSummaryPayload($searchSchema);

        $user->executeSalesTask($task, $payload);
        return $payload->result;
    }
}
