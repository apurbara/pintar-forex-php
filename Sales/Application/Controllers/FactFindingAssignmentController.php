<?php

namespace Sales\Application\Controllers;

use Company\Application\EventHandler\CustomerVerifiedEventHandler;
use Company\Domain\Model\CustomerJourney;
use Company\Domain\Model\Manager\Sales as Sales2;
use Company\Domain\Model\Manager\Sales\FactFindingAssignment as FactFindingAssignment2;
use Company\Domain\Model\Manager\Sales\StrikingAssignment;
use Company\Domain\Service\SalesFinderService;
use GraphQL\Type\Definition\IntType;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Event\Dispatcher;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Domain\DependencyModel\Customer\VerificationReport;
use Sales\Domain\DependencyModel\Customer\VerificationReportData;
use Sales\Domain\DependencyModel\CustomerVerification;
use Sales\Domain\Event\CustomerVerified;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\FactFindingAssignment;
use Sales\Domain\Task\FactFindingAssignment\MarkCustomerVerified;
use Sales\Domain\Task\FactFindingAssignment\SubmitVerificationReport;
use Sales\Domain\Task\FactFindingAssignment\ViewFactFindingAssignmentDetail;
use Sales\Domain\Task\FactFindingAssignment\ViewFactFindingAssignmentList;
use Sales\Domain\Task\FactFindingAssignment\ViewTotalFactFindingAssignment;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineFactFindingAssignmentRepository;
use Shared\Application\Controllers\Controller;

#[GraphqlMapableController(entity: FactFindingAssignment::class)]
class FactFindingAssignmentController extends Controller
{

    private function repository(): DoctrineFactFindingAssignmentRepository
    {
        return $this->em->getRepository(FactFindingAssignment::class);
    }

    //
    private function buildCustomerVerifiedEventHandler()
    {
        $strikingAssignmentRepository = $this->em->getRepository(StrikingAssignment::class);
        $factFindingAssignmentRepository = $this->em->getRepository(FactFindingAssignment2::class);
        $salesFinderService = new SalesFinderService($this->em->getRepository(Sales2::class));
        $customerJourneyRepository = $this->em->getRepository(CustomerJourney::class);
        return new CustomerVerifiedEventHandler(
                $strikingAssignmentRepository, $factFindingAssignmentRepository, $salesFinderService, $customerJourneyRepository);
    }

    #[Mutation]
    public function markCustomerVerified(Sales $sales, string $id)
    {
        $repository = $this->repository();

        $customerVerificationRepository = $this->em->getRepository(CustomerVerification::class);
        $dispatcher = new Dispatcher();
        $dispatcher->addAsynchronousListener(CustomerVerified::eventName(), $this->buildCustomerVerifiedEventHandler());

        $task = new MarkCustomerVerified($repository, $customerVerificationRepository, $dispatcher);

        $sales->executeTask($task, $id);
        $this->em->flush();

        $dispatcher->publishAsynchronous();
        return $repository->queryOneById($id);
    }

    //define GraphQL schema manually
    public function submitCustomerVerificationReport(Sales $sales, string $FactFindingAssignment_id, InputRequest $input)
    {
        $customerVerificationRepository = $this->em->getRepository(CustomerVerification::class);
        $task = new SubmitVerificationReport($this->repository(), $customerVerificationRepository);
        $payload = (new VerificationReportData())
                ->setFactFindingAssignmentId($FactFindingAssignment_id)
                ->setCustomerVerificationId($input->get('CustomerVerification_id'))
                ->setNote($input->get('note'));

        $sales->executeTask($task, $payload);
        $this->em->flush();
        
        return $this->em->getRepository(VerificationReport::class)->queryOneById($payload->id);
    }

    //
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function factFindingAssignmentList(Sales $sales, InputRequest $input)
    {
        $task = new ViewFactFindingAssignmentList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }

    #[Query]
    public function factFindingAssignmentDetail(Sales $sales, string $id)
    {

        $task = new ViewFactFindingAssignmentDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }

    #[Query(responseWrapper: Query::SUMMARY_RESPONSE_WRAPPER, responseType: IntType::class)]
    public function totalFactFindingAssignment(Sales $sales, InputRequest $input)
    {
        $task = new ViewTotalFactFindingAssignment($this->repository());
        $payload = $this->buildViewSummaryPayload($input);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }
}
