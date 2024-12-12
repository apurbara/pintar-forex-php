<?php

namespace Sales\Application\Controllers\CustomerAssignment;

use Resources\Application\InputRequest;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Sales\Domain\DependencyModel\Customer;
use Sales\Domain\DependencyModel\CustomerData;
use Sales\Domain\DependencyModel\Province\City;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\FactFindingAssignment;
use Sales\Domain\Model\Sales\GreetingAssignment;
use Sales\Domain\Model\Sales\StrikingAssignment;
use Sales\Domain\Task\Customer\UpdateCustomer;
use Sales\Domain\Task\Customer\UpdateCustomerPayload;
use Sales\Domain\Task\Customer\UpdateCustomerRating;
use Sales\Domain\Task\Customer\UpdateCustomerRatingPayload;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerRepository;
use Shared\Application\Controllers\Controller;
use Shared\Domain\Enum\SalesRole;

#[GraphqlMapableController(entity: Customer::class)]
class CustomerController extends Controller
{

    private function repository(): DoctrineCustomerRepository
    {
        return $this->em->getRepository(Customer::class);
    }

    //
    #[Mutation]
    public function updateCustomerRating(Sales $sales, string $customerAssignmentId, InputRequest $input)
    {
        $customerAssignmentRepository = match ($sales->getRole()) {
            SalesRole::GREETER => $this->em->getRepository(GreetingAssignment::class),
            SalesRole::FACT_FINDER => $this->em->getRepository(FactFindingAssignment::class),
            SalesRole::STRIKER => $this->em->getRepository(StrikingAssignment::class),
        };
        $task = new UpdateCustomerRating($customerAssignmentRepository);
        $payload = (new UpdateCustomerRatingPayload())
                ->setCustomerAssignmentId($customerAssignmentId)
                ->setRating($input->get('rating'));
        $sales->executeTask($task, $payload);
        $this->em->flush();
        
        return $this->repository()->aCustomerAssociateWithAssignment($customerAssignmentId);
    }
    
    #[Mutation]
    public function updateCustomer(Sales $sales, string $customerAssignmentId, InputRequest $input)
    {
        $customerAssignmentRepository = match ($sales->getRole()) {
            SalesRole::GREETER => $this->em->getRepository(GreetingAssignment::class),
            SalesRole::FACT_FINDER => $this->em->getRepository(FactFindingAssignment::class),
            SalesRole::STRIKER => $this->em->getRepository(StrikingAssignment::class),
        };
        
        $cityRepository = $this->em->getRepository(City::class);
        $task = new UpdateCustomer($customerAssignmentRepository, $cityRepository);

        $customerInput = $input->get('customer');
        $customerData = (new CustomerData())
                ->setCityId($input->get('City_id'))
                ->setName($input->get('name'))
                ->setEmail($input->get('email'))
                ->setBio($input->get('bio'));
                
        $payload = (new UpdateCustomerPayload())
                ->setCustomerData($customerData)
                ->setCustomerAssignmentId($customerAssignmentId);

        $sales->executeTask($task, $payload);
        $this->em->flush();

        return $this->repository()->aCustomerAssociateWithAssignment($customerAssignmentId);
    }
}
