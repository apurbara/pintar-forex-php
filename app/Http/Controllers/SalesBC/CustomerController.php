<?php

namespace App\Http\Controllers\SalesBC;

use App\Http\Controllers\Controller;
use Resources\Application\InputRequest;
use Resources\Event\Dispatcher;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Sales\Application\Listener\AllocateInitialSalesActivityScheduleListener;
use Sales\Domain\Model\AreaStructure\Area;
use Sales\Domain\Model\AreaStructure\Area\Customer;
use Sales\Domain\Model\AreaStructure\Area\CustomerData;
use Sales\Domain\Model\CustomerJourney;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;
use Sales\Domain\Model\SalesActivity;
use Sales\Domain\Task\AssignedCustomer\RegisterNewCustomerPayload;
use Sales\Domain\Task\AssignedCustomer\RegisterNewCustomerTask;
use SharedContext\Domain\Event\CustomerAssignedEvent;

#[GraphqlMapableController(entity: Customer::class)]
class CustomerController extends Controller
{
    #[Mutation(responseType: AssignedCustomer::class)]
    public function registerNewCustomer(SalesRoleInterface $user, InputRequest $input)
    {
        $repository = $this->em->getRepository(AssignedCustomer::class);
        $areaRepository = $this->em->getRepository(Area::class);
        $customerRepository = $this->em->getRepository(Customer::class);
        $customerJourneyRepository = $this->em->getRepository(CustomerJourney::class);
        $dispatcher = new Dispatcher();

        $salesRepository = $this->em->getRepository(Sales::class);
        $salesActivityScheduleRepository = $this->em->getRepository(SalesActivitySchedule::class);
        $salesActivityRepository = $this->em->getRepository(SalesActivity::class);
        $listener = new AllocateInitialSalesActivityScheduleListener
                ($salesRepository, $salesActivityScheduleRepository, $repository, $salesActivityRepository,
                $user->getPersonnelId(), $user->getSalesId());
        
        $dispatcher->addTransactionalListener(CustomerAssignedEvent::eventName(), $listener);

        $task = new RegisterNewCustomerTask($repository, $areaRepository, $customerRepository,
                $customerJourneyRepository, $dispatcher);

        $areaId = $input->get('Area_id');
        $name = $input->get('name');
        $email = $input->get('email');
        $phone = $input->get('phone');
        $customerData = new CustomerData($name, $email, $phone);
        $payload = new RegisterNewCustomerPayload($areaId, $customerData);
        $user->executeSalesTask($task, $payload);
        
//        try {
            $dispatcher->publishTransactional();
//        } catch (Exception $ex) {
//            
//        }
        
        return $repository->queryOneById($payload->id);
    }
}
