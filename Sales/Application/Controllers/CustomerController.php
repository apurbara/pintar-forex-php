<?php

namespace Sales\Application\Controllers;

use Resources\Application\InputRequest;
use Resources\Event\Dispatcher;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Sales\Domain\DependencyModel\Customer;
use Sales\Domain\DependencyModel\CustomerData;
use Sales\Domain\DependencyModel\CustomerJourney;
use Sales\Domain\DependencyModel\Province\City;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Task\CustomerAssignment\RegisterNewCustomerPayload;
use Sales\Domain\Task\CustomerAssignment\RegisterNewCustomerTask;

#[GraphqlMapableController(entity: Customer::class)]
class CustomerController extends BaseController
{
    #[Mutation(responseType: CustomerAssignment::class)]
    public function registerNewCustomer(Sales $sales, InputRequest $input)
    {
        $repository = $this->em->getRepository(CustomerAssignment::class);
        $cityRepository = $this->em->getRepository(City::class);
        $customerRepository = $this->em->getRepository(Customer::class);
        $customerJourneyRepository = $this->em->getRepository(CustomerJourney::class);
        $dispatcher = new Dispatcher();

        $task = new RegisterNewCustomerTask($repository, $cityRepository, $customerRepository, $customerJourneyRepository, $dispatcher);

        $customerData = (new CustomerData())
                ->setCityId($input->get('City_id'))
                ->setName($input->get('name'))
                ->setEmail($input->get('email'))
                ->setPhone($input->get('phone'))
                ->setSource($input->get('source'));
        $payload = (new RegisterNewCustomerPayload())
                ->setCustomerData($customerData);
        
        $this->executeSalesMutationTask($sales, $task, $payload);
        return $repository->queryOneById($payload->id);
    }
}
