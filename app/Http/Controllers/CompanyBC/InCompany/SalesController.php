<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\Personnel;
use Company\Domain\Model\Personnel\Sales;
use Company\Domain\Model\Personnel\SalesData;
use Company\Domain\Task\InCompany\Sales\AssignSalesTask;
use Company\Domain\Task\InCompany\Sales\CancelSalesAssignment;
use Company\Domain\Task\InCompany\Sales\ViewSalesDetailTask;
use Company\Domain\Task\InCompany\Sales\ViewSalesListTask;
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
    public function assignSales(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $repository = $this->repository();
        $personnelRepository = $this->em->getRepository(Personnel::class);
        $areaRepository = $this->em->getRepository(Area::class);

        $task = new AssignSalesTask($repository, $personnelRepository, $areaRepository);

        $type = $input->get('type');
        $payload = (new SalesData($type))
                ->setPersonnelId($input->get('Personnel_id'))
                ->setAreaId($input->get('Area_id'));

        $user->executeTaskInCompany($task, $payload);
        return $this->repository()->fetchOneByIdOrDie($payload->id);
    }

    #[Mutation]
    public function cancelSalesAssignment(CompanyUserRoleInterface $user, string $id)
    {
        $repository = $this->repository();
        $task = new CancelSalesAssignment($repository);

        $user->executeTaskInCompany($task, $id);
        return $this->repository()->fetchOneByIdOrDie($id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function salesList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewSalesListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeTaskInCompany($task, $payload);

        return $payload->result;
    }

    #[Query]
    public function salesDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewSalesDetailTask($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeTaskInCompany($task, $payload);

        return $payload->result;
    }
}
