<?php

namespace App\Http\Controllers\SalesBC;

use App\Http\Controllers\Controller;
use App\Http\Controllers\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Sales\Domain\Model\AreaStructure\Area\Customer\VerificationReport;
use Sales\Domain\Model\AreaStructure\Area\Customer\VerificationReportData;
use Sales\Domain\Model\CustomerVerification;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Domain\Task\VerificationReport\SubmitVerificationReportTask;
use Sales\Domain\Task\VerificationReport\ViewVerificationReportDetailTask;
use Sales\Domain\Task\VerificationReport\ViewVerificationReportListTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineVerificationReportRepository;

class VerificationReportController extends Controller
{

    protected function repository(): DoctrineVerificationReportRepository
    {
        return $this->em->getRepository(VerificationReport::class);
    }

    //
    public function submit(SalesRoleInterface $user, string $assignedCustomerId, InputRequest $input)
    {
        $assignedCustomerRepository = $this->em->getRepository(AssignedCustomer::class);
        $customerVerificationRepository = $this->em->getRepository(CustomerVerification::class);

        $task = new SubmitVerificationReportTask($assignedCustomerRepository, $customerVerificationRepository);

        $note = $input->get('note');
        $customerVerificationId = $input->get('customerVerificationId');
        $payload = (new VerificationReportData($note))
                ->setAssignedCustomerId($assignedCustomerId)
                ->setCustomerVerificationId($customerVerificationId);

        $user->executeSalesTask($task, $payload);

        return $this->repository()->aVerificationReportOnAssignedCustomerAssociateWithCustomerVerificationId(
                        $assignedCustomerId, $customerVerificationId);
    }

    public function viewList(SalesRoleInterface $user, InputRequest $input)
    {
        $task = new ViewVerificationReportListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeSalesTask($task, $payload);

        return $payload->result;
    }

    public function viewDetail(SalesRoleInterface $user, string $id)
    {
        $task = new ViewVerificationReportDetailTask($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeSalesTask($task, $payload);

        return $payload->result;
    }
}
