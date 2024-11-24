<?php

namespace Sales\Application\Controllers;

use Company\Application\EventHandler\CustomerValidatedEventHandler;
use Company\Domain\Model\Manager\Sales as Sales2;
use Company\Domain\Model\Manager\Sales\FactFindingAssignment;
use Company\Domain\Model\Manager\Sales\GreetingAssignment as GreetingAssignment2;
use Company\Domain\Service\SalesFinderService;
use GraphQL\Type\Definition\IntType;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Event\Dispatcher;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Domain\DependencyModel\CustomerData;
use Sales\Domain\DependencyModel\Province\City;
use Sales\Domain\Event\CustomerValidated;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\GreetingAssignment;
use Sales\Domain\Task\GreetingAssignment\RecycleCustomer;
use Sales\Domain\Task\GreetingAssignment\UpdateCustomer;
use Sales\Domain\Task\GreetingAssignment\UpdateCustomerPayload;
use Sales\Domain\Task\GreetingAssignment\ValidateCustomer;
use Sales\Domain\Task\GreetingAssignment\ViewGreetingAssignmentDetail;
use Sales\Domain\Task\GreetingAssignment\ViewGreetingAssignmentList;
use Sales\Domain\Task\GreetingAssignment\ViewTotalGreetingAssignment;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineGreetingAssignmentRepository;
use Shared\Application\Controllers\Controller;

#[GraphqlMapableController(entity: GreetingAssignment::class)]
class GreetingAssignmentController extends Controller
{

    private function repository(): DoctrineGreetingAssignmentRepository
    {
        return $this->em->getRepository(GreetingAssignment::class);
    }

    //
    #[Mutation]
    public function updateCustomerBio(Sales $sales, InputRequest $input)
    {
        $cityRepository = $this->em->getRepository(City::class);
        $task = new UpdateCustomer($this->repository(), $cityRepository);

        $customerInput = $input->get('customer');
        $customerData = (new CustomerData())
                ->setCityId($customerInput['City_id'])
                ->setEmail($customerInput['email'])
                ->setName($customerInput['name']);
        $payload = (new UpdateCustomerPayload())
                ->setCustomerData($customerData)
                ->setId($input->get('id'));

        $sales->executeTask($task, $payload);
        $this->em->flush();

        return $this->repository()->queryOneById($payload->id);
    }

    private function buildCustomerValidatedEventHandler()
    {
        $factFindingAssignmentRepository = $this->em->getRepository(FactFindingAssignment::class);
        $greetingAssignmentRepository = $this->em->getRepository(GreetingAssignment2::class);
        $salesFinderService = new SalesFinderService($this->em->getRepository(Sales2::class));
        return new CustomerValidatedEventHandler(
                $factFindingAssignmentRepository, $greetingAssignmentRepository, $salesFinderService);
    }

    #[Mutation]
    public function validateCustomer(Sales $sales, string $id)
    {
        $repository = $this->repository();

        $dispatcher = new Dispatcher();
        $dispatcher->addAsynchronousListener(CustomerValidated::eventName(), $this->buildCustomerValidatedEventHandler());

        $task = new ValidateCustomer($repository, $dispatcher);

        $sales->executeTask($task, $id);
        $this->em->flush();

        $dispatcher->publishAsynchronous();
        return $repository->queryOneById($id);
    }

    #[Mutation]
    public function recycleCustomer(Sales $sales, string $id)
    {
        $task = new RecycleCustomer($this->repository());

        $sales->executeTask($task, $id);
        $this->em->flush();
        return $this->repository()->queryOneById($id);
    }

    //
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function greetingAssignmentList(Sales $sales, InputRequest $input)
    {
        $task = new ViewGreetingAssignmentList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }

    #[Query]
    public function greetingAssignmentDetail(Sales $sales, string $id)
    {

        $task = new ViewGreetingAssignmentDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::SUMMARY_RESPONSE_WRAPPER, responseType: IntType::class)]
    public function totalGreetingAssignment(Sales $sales, InputRequest $input)
    {
        $task = new ViewTotalGreetingAssignment($this->repository());
        $payload = $this->buildViewSummaryPayload($input);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }
}
