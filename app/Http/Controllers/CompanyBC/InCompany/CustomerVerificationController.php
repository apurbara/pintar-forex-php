<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\CustomerVerification;
use Company\Domain\Model\CustomerVerificationData;
use Company\Domain\Task\InCompany\CustomerVerification\AddCustomerVerificationTask;
use Company\Domain\Task\InCompany\CustomerVerification\DisableCustomerVerification;
use Company\Domain\Task\InCompany\CustomerVerification\EnableCustomerVerification;
use Company\Domain\Task\InCompany\CustomerVerification\UpdateCustomerVerification;
use Company\Domain\Task\InCompany\CustomerVerification\ViewAllActiveCustomerVerification;
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

    private function createData(InputRequest $input): CustomerVerificationData
    {
        return (new CustomerVerificationData($this->createLabelData($input)))
                        ->setPosition($input->get('position'))
                        ->setWeight($input->get('weight'));
    }

    //
    #[Mutation]
    public function addCustomerVerification(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();

        $task = new AddCustomerVerificationTask($repository);
        $payload = $this->createData($input);
        
        $user->executeTaskInCompany($task, $payload);
        return $repository->fetchOneByIdOrDie($payload->id);
    }
    
    #[Mutation]
    public function updateCustomerVerification(CompanyUserRoleInterface $user, string $id, InputRequest $input)
    {
        $repository = $this->repository();

        $task = new UpdateCustomerVerification($repository);
        $payload = $this->createData($input)
                ->setId($id);
        
        $user->executeTaskInCompany($task, $payload);
        return $repository->fetchOneByIdOrDie($payload->id);
    }
    
    #[Mutation]
    public function disableCustomerVerification(CompanyUserRoleInterface $user, string $id)
    {
        $repository = $this->repository();
        $task = new DisableCustomerVerification($repository);
        
        $user->executeTaskInCompany($task, $id);
        return $repository->fetchOneByIdOrDie($id);
    }
    
    #[Mutation]
    public function enableCustomerVerification(CompanyUserRoleInterface $user, string $id)
    {
        $repository = $this->repository();
        $task = new EnableCustomerVerification($repository);
        
        $user->executeTaskInCompany($task, $id);
        return $repository->fetchOneByIdOrDie($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function customerVerificationList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewCustomerVerificationListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeTaskInCompany($task, $payload);

        return $payload->result;
    }

    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER)]
    public function viewAllActiveCustomerVerification(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewAllActiveCustomerVerification($this->repository());
        $payload = $this->buildViewAllListPayload($input);
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
