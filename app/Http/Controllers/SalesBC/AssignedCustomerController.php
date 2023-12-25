<?php

namespace App\Http\Controllers\SalesBC;

use App\Http\Controllers\Controller;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Domain\TaskPayload\ViewSummaryPayload;
use Resources\Event\Dispatcher;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Application\Listener\AllocateInitialSalesActivityScheduleListener;
use Sales\Domain\Model\AreaStructure\Area;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\CustomerJourney;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomerData;
use Sales\Domain\Model\SalesActivity;
use Sales\Domain\Task\AssignedCustomer\RegisterNewCustomerPayload;
use Sales\Domain\Task\AssignedCustomer\RegisterNewCustomerTask;
use Sales\Domain\Task\AssignedCustomer\UpdateJourney;
use Sales\Domain\Task\AssignedCustomer\ViewAssignedCustomerDetail;
use Sales\Domain\Task\AssignedCustomer\ViewAssignedCustomerList;
use Sales\Domain\Task\AssignedCustomer\ViewTotalCustomerAssignment;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineAssignedCustomerRepository;
use SharedContext\Domain\Event\CustomerAssignedEvent;

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
