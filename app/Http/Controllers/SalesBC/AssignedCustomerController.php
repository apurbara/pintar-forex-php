<?php

namespace App\Http\Controllers\SalesBC;

use App\Http\Controllers\Controller;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Domain\TaskPayload\ViewSummaryPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Domain\Model\AreaStructure\Area;
use Sales\Domain\Model\AreaStructure\Area\CustomerData;
use Sales\Domain\Model\CustomerJourney;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomerData;
use Sales\Domain\Task\AssignedCustomer\UpdateCustomer;
use Sales\Domain\Task\AssignedCustomer\UpdateCustomerPayload;
use Sales\Domain\Task\AssignedCustomer\UpdateJourney;
use Sales\Domain\Task\AssignedCustomer\ViewAssignedCustomerDetail;
use Sales\Domain\Task\AssignedCustomer\ViewAssignedCustomerList;
use Sales\Domain\Task\AssignedCustomer\ViewTotalCustomerAssignment;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineAssignedCustomerRepository;

#[GraphqlMapableController(entity: AssignedCustomer::class)]
class AssignedCustomerController extends Controller
{

    protected function repository(): DoctrineAssignedCustomerRepository
    {
        return $this->em->getRepository(AssignedCustomer::class);
    }

    #[Mutation]
    public function updateAssignedCustomerJourney(SalesRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();
        $customerJourneyRepository = $this->em->getRepository(CustomerJourney::class);
        $task = new UpdateJourney($repository, $customerJourneyRepository);
        $payload = (new AssignedCustomerData())
                ->setId($input->get('id'))
                ->setCustomerJourneyId($input->get('CustomerJourney_id'));
        $user->executeSalesTask($task, $payload);

        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function updateCustomerBio(SalesRoleInterface $user, InputRequest $input)
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
    public function assignedCustomerList(SalesRoleInterface $user, InputRequest $input)
    {
        $task = new ViewAssignedCustomerList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeSalesTask($task, $payload);

        return $payload->result;
    }

    #[Query]
    public function assignedCustomerDetail(SalesRoleInterface $user, string $id)
    {
        $task = new ViewAssignedCustomerDetail($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeSalesTask($task, $payload);

        return $payload->result;
    }

    public function totalCustomerAssignment(SalesRoleInterface $user, InputRequest $input)
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
