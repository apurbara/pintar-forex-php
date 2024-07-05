<?php

namespace Sales\Application\Controllers;

use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Domain\DependencyModel\Customer\VerificationReport;
use Sales\Domain\DependencyModel\Customer\VerificationReportData;
use Sales\Domain\DependencyModel\CustomerVerification;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Task\VerificationReport\SubmitVerificationReportTask;
use Sales\Domain\Task\VerificationReport\ViewVerificationReportDetailTask;
use Sales\Domain\Task\VerificationReport\ViewVerificationReportListTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineVerificationReportRepository;

#[GraphqlMapableController(entity: VerificationReport::class)]
class VerificationReportController extends BaseController
{

    protected function repository(): DoctrineVerificationReportRepository
    {
        return $this->em->getRepository(VerificationReport::class);
    }

    //
    public function submitCustomerVerificationReport(Sales $sales, string $CustomerAssignment_id, InputRequest $input)
    {
        $customerAssignmentRepository = $this->em->getRepository(CustomerAssignment::class);
        $customerVerificationRepository = $this->em->getRepository(CustomerVerification::class);

        $task = new SubmitVerificationReportTask($customerAssignmentRepository, $customerVerificationRepository);

        $payload = (new VerificationReportData())
                ->setNote($input->get('note'))
                ->setCustomerAssignmentId($CustomerAssignment_id)
                ->setCustomerVerificationId($input->get('CustomerVerification_id'));

        $this->executeSalesMutationTask($sales, $task, $payload);
        return $this->repository()->aVerificationReportOnCustomerAssignmentAssociateWithCustomerVerificationId(
                        $CustomerAssignment_id, $input->get('CustomerVerification_id'));
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function verificationReportList(Sales $sales, InputRequest $input)
    {
        $task = new ViewVerificationReportListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }

    #[Query]
    public function verificationReportDetail(Sales $sales, string $id)
    {
        $task = new ViewVerificationReportDetailTask($this->repository());
        $payload = new ViewDetailPayload($id);

        $sales->executeTask($task, $payload);
        return $payload->result;
    }
}
