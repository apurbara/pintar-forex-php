<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use App\Http\Controllers\InputRequest;
use Company\Domain\Model\CustomerVerification;
use Company\Domain\Model\CustomerVerificationData;
use Company\Domain\Task\InCompany\CustomerVerification\AddCustomerVerificationTask;
use Company\Domain\Task\InCompany\CustomerVerification\ViewCustomerVerificationDetailTask;
use Company\Domain\Task\InCompany\CustomerVerification\ViewCustomerVerificationListTask;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerVerificationRepository;
use Resources\Domain\TaskPayload\ViewDetailPayload;

class CustomerVerificationController extends Controller
{

    protected function repository(): DoctrineCustomerVerificationRepository
    {
        return $this->em->getRepository(CustomerVerification::class);
    }

    //
    public function add(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();

        $task = new AddCustomerVerificationTask($repository);
        $payload = new CustomerVerificationData($this->createLabelData($input));
        $user->executeTaskInCompany($task, $payload);
        //
        return $repository->fetchOneByIdOrDie($payload->id);
    }

    public function viewList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewCustomerVerificationListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeTaskInCompany($task, $payload);

        return $payload->result;
    }

    public function viewDetail(CompanyUserRoleInterface $user, string $customerVerificationId)
    {
        $task = new ViewCustomerVerificationDetailTask($this->repository());
        $payload = new ViewDetailPayload($customerVerificationId);
        $user->executeTaskInCompany($task, $payload);

        return $payload->result;
    }
}
