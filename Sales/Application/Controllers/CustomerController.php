<?php

namespace Sales\Application\Controllers;

use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Sales\Domain\DependencyModel\Customer;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\FactFindingAssignment;
use Sales\Domain\Model\Sales\GreetingAssignment;
use Sales\Domain\Model\Sales\StrikingAssignment;
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
    public function updateCustomerRating(Sales $sales, string $customerAssignmentId, int $rating)
    {
        $customerAssignmentRepository = match ($sales->getRole()) {
            SalesRole::GREETER => $this->em->getRepository(GreetingAssignment::class),
            SalesRole::FACT_FINDER => $this->em->getRepository(FactFindingAssignment::class),
            SalesRole::STRIKER => $this->em->getRepository(StrikingAssignment::class),
        };
        $task = new UpdateCustomerRating($customerAssignmentRepository);
        $payload = (new UpdateCustomerRatingPayload())
                ->setId($customerAssignmentId)
                ->setRating($rating);
        $sales->executeTask($task, $payload);
        $this->em->flush();
        
        return $this->repository()->aCustomerAssociateWithAssignment($customerAssignmentId);
    }
}
