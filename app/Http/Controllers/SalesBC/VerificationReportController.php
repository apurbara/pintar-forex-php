<?php

namespace App\Http\Controllers\SalesBC;

use App\Http\Controllers\Controller;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Domain\Model\AreaStructure\Area\Customer\VerificationReport;
use Sales\Domain\Model\AreaStructure\Area\Customer\VerificationReportData;
use Sales\Domain\Model\CustomerVerification;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Task\VerificationReport\SubmitVerificationReportTask;
use Sales\Domain\Task\VerificationReport\ViewVerificationReportDetailTask;
use Sales\Domain\Task\VerificationReport\ViewVerificationReportListTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineVerificationReportRepository;

#[GraphqlMapableController(entity: VerificationReport::class)]
class VerificationReportController extends Controller
{

    protected function repository(): DoctrineVerificationReportRepository
    {
        return $this->em->getRepository(VerificationReport::class);
    }

    //
    public function submitCustomerVerificationReport(SalesRoleInterface $user, string $AssignedCustomer_id, InputRequest $input)
    {
        $assignedCustomerRepository = $this->em->getRepository(AssignedCustomer::class);
        $customerVerificationRepository = $this->em->getRepository(CustomerVerification::class);

        $task = new SubmitVerificationReportTask($assignedCustomerRepository, $customerVerificationRepository);

        $note = $input->get('note');
        $customerVerificationId = $input->get('CustomerVerification_id');
        $payload = (new VerificationReportData($note))
                ->setAssignedCustomerId($AssignedCustomer_id)
                ->setCustomerVerificationId($customerVerificationId);

        $user->executeSalesTask($task, $payload);

        return $this->repository()->aVerificationReportOnAssignedCustomerAssociateWithCustomerVerificationId(
                        $AssignedCustomer_id, $customerVerificationId);
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
