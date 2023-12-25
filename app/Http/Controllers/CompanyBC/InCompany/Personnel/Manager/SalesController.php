<?php

namespace App\Http\Controllers\CompanyBC\InCompany\Personnel\Manager;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Manager;
use Company\Domain\Model\Personnel\Manager\Sales;
use Company\Domain\Model\Personnel\Manager\SalesData;
use Company\Domain\Task\InCompany\Personnel\Manager\Sales\AssignSalesTask;
use Company\Domain\Task\InCompany\Personnel\Manager\Sales\ViewSalesDetailTask;
use Company\Domain\Task\InCompany\Personnel\Manager\Sales\ViewSalesListTask;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: Sales::class)]
class SalesController extends Controller
{
    protected function repository(): DoctrineSalesRepository
    {
        return $this->em->getRepository(Sales::class);
    }
    
    //
    #[Mutation]
    public function assignSales(CompanyUserRoleInterface $user, ?string $managerId, InputRequest $input)
    {
        $repository = $this->repository();
        $managerRepository = $this->em->getRepository(Manager::class);
        $personnelRepository = $this->em->getRepository(Personnel::class);
        $areaRepository = $this->em->getRepository(Area::class);
        
        $task = new AssignSalesTask($repository, $managerRepository, $personnelRepository, $areaRepository);
        
        $type = $input->get('type');
        $payload = (new SalesData($type))
                ->setManagerId($managerId ?? $input->get('Manager_id'))
                ->setPersonnelId($input->get('Personnel_id'))
                ->setAreaId($input->get('Area_id'));
        
        $user->executeTaskInCompany($task, $payload);
        //
        return $this->repository()->fetchOneByIdOrDie($payload->id);
    }
    
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewSalesList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewSalesListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeTaskInCompany($task, $payload);
        
        return $payload->result;
    }
    
    #[Query]
    public function viewSalesDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewSalesDetailTask($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeTaskInCompany($task, $payload);
        
        return $payload->result;
    }
}
