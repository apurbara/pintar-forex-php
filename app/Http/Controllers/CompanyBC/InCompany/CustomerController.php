<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\AreaStructure\Area\Customer;
use Company\Domain\Task\InCompany\Customer\ViewCustomerList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerRepository;
use Resources\Application\InputRequest;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: Customer::class)]
class CustomerController extends Controller
{
    protected function repository(): DoctrineCustomerRepository
    {
        return $this->em->getRepository(Customer::class);
    }
    
    //
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function customerList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewCustomerList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
