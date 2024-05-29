<?php

namespace App\Http\Controllers\SalesBC\BySales;

use App\Http\Controllers\Controller;
use Resources\Application\InputRequest;
use Resources\Event\Dispatcher;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Sales\Application\Listener\AllocateInitialSalesActivityScheduleListener;
use Sales\Domain\DependencyModel\AreaStructure\Area;
use Sales\Domain\DependencyModel\AreaStructure\Area\Customer;
use Sales\Domain\DependencyModel\AreaStructure\Area\CustomerData;
use Sales\Domain\DependencyModel\CustomerJourney;
use Sales\Domain\DependencyModel\SalesActivity;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Model\Sales\CustomerAssignment\SalesActivitySchedule;
use Sales\Domain\Task\BySales\CustomerAssignment\RegisterNewCustomerPayload;
use Sales\Domain\Task\BySales\CustomerAssignment\RegisterNewCustomerTask;
use SharedContext\Domain\Event\CustomerAssignedEvent;

#[GraphqlMapableController(entity: Customer::class)]
class CustomerController extends Controller
{
    #[Mutation(responseType: CustomerAssignment::class)]
    public function registerNewCustomer(SalesRoleInterface $user, InputRequest $input)
    {
        $repository = $this->em->getRepository(CustomerAssignment::class);
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
        $customerData = (new CustomerData($name, $email, $phone))
                ->setSource($input->get('source'));
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
