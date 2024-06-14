<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\Sales;
use Company\Domain\Model\SalesData;
use Company\Domain\Task\InCompany\Sales\AddSales;
use Company\Domain\Task\InCompany\Sales\CancelSales;
use Company\Domain\Task\InCompany\Sales\ViewAllSales;
use Company\Domain\Task\InCompany\Sales\ViewSalesDetail;
use Company\Domain\Task\InCompany\Sales\ViewSalesList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use SharedContext\Domain\ValueObject\AccountInfoData;

#[GraphqlMapableController(entity: Sales::class)]
class SalesController extends Controller
{

    protected function repository(): DoctrineSalesRepository
    {
        return $this->em->getRepository(Sales::class);
    }
    
    private function createSalesData(InputRequest $input): SalesData
    {
        $name = $input->get('name');
        $email = $input->get('email');
        $password = $input->get('password');
        $accountInfoData = new AccountInfoData($name, $email, $password);
        return (new SalesData())
        ->setAccountInfoData($accountInfoData)
                ->setAreaId($input->get('Area_id'))
                ->setType($input->get('type'));
    }

    //
    #[Mutation]
    public function addSales(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();
        $areaRepository = $this->em->getRepository(Area::class);

        $task = new AddSales($repository, $areaRepository);
        $payload = $this->createSalesData($input);

        $user->executeTaskInCompany($task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Mutation]
    public function cancelSalesAssignment(CompanyUserRoleInterface $user, string $id)
    {
        $repository = $this->repository();
        $task = new CancelSales($repository);

        $user->executeTaskInCompany($task, $id);
        return $this->repository()->queryOneById($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewSalesList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewSalesList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeTaskInCompany($task, $payload);

        return $payload->result;
    }

    #[Query]
    public function viewSalesDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewSalesDetail($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeTaskInCompany($task, $payload);

        return $payload->result;
    }

    #[Query(responseWrapper:Query::LIST_RESPONSE_WRAPPER)]
    public function viewAllSales(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewAllSales($this->repository());
        $payload = $this->buildViewAllListPayload($input);
        
        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
