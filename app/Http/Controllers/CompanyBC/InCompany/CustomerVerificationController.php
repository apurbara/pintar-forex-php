<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\CustomerVerification;
use Company\Domain\Model\CustomerVerificationData;
use Company\Domain\Task\InCompany\CustomerVerification\AddCustomerVerificationTask;
use Company\Domain\Task\InCompany\CustomerVerification\ViewCustomerVerificationDetailTask;
use Company\Domain\Task\InCompany\CustomerVerification\ViewCustomerVerificationListTask;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerVerificationRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: CustomerVerification::class)]
class CustomerVerificationController extends Controller
{

    protected function repository(): DoctrineCustomerVerificationRepository
    {
        return $this->em->getRepository(CustomerVerification::class);
    }

    //
    #[Mutation]
    public function addCustomerVerification(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();

        $task = new AddCustomerVerificationTask($repository);
        $payload = (new CustomerVerificationData($this->createLabelData($input)))
                ->setWeight($input->get('weight'))
                ->setPosition($input->get('position'));
        $user->executeTaskInCompany($task, $payload);
        //
        return $repository->fetchOneByIdOrDie($payload->id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function customerVerificationList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewCustomerVerificationListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeTaskInCompany($task, $payload);

        return $payload->result;
    }

    #[Query]
    public function customerVerificationDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewCustomerVerificationDetailTask($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeTaskInCompany($task, $payload);

        return $payload->result;
    }
}
