<?php

namespace App\Http\Controllers\SalesBC\BySales;

use App\Http\Controllers\Controller;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Domain\DependencyModel\AreaStructure\Area\Customer\VerificationReport;
use Sales\Domain\DependencyModel\AreaStructure\Area\Customer\VerificationReportData;
use Sales\Domain\DependencyModel\CustomerVerification;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Domain\Task\BySales\VerificationReport\SubmitVerificationReportTask;
use Sales\Domain\Task\BySales\VerificationReport\ViewVerificationReportDetailTask;
use Sales\Domain\Task\BySales\VerificationReport\ViewVerificationReportListTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineVerificationReportRepository;

#[GraphqlMapableController(entity: VerificationReport::class)]
class VerificationReportController extends Controller
{

    protected function repository(): DoctrineVerificationReportRepository
    {
        return $this->em->getRepository(VerificationReport::class);
    }

    //
    public function submitCustomerVerificationReport(SalesRoleInterface $user, string $CustomerAssignment_id, InputRequest $input)
    {
        $customerAssignmentRepository = $this->em->getRepository(CustomerAssignment::class);
        $customerVerificationRepository = $this->em->getRepository(CustomerVerification::class);

        $task = new SubmitVerificationReportTask($customerAssignmentRepository, $customerVerificationRepository);

        $note = $input->get('note');
        $customerVerificationId = $input->get('CustomerVerification_id');
        $payload = (new VerificationReportData($note))
                ->setCustomerAssignmentId($CustomerAssignment_id)
                ->setCustomerVerificationId($customerVerificationId);

        $user->executeSalesTask($task, $payload);

        return $this->repository()->aVerificationReportOnCustomerAssignmentAssociateWithCustomerVerificationId(
                        $CustomerAssignment_id, $customerVerificationId);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function verificationReportList(SalesRoleInterface $user, InputRequest $input)
    {
        $task = new ViewVerificationReportListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeSalesTask($task, $payload);

        return $payload->result;
    }

    #[Query]
    public function verificationReportDetail(SalesRoleInterface $user, string $id)
    {
        $task = new ViewVerificationReportDetailTask($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeSalesTask($task, $payload);

        return $payload->result;
    }
}
