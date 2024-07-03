<?php

namespace Manager\Application\Controllers;

use Manager\Domain\Model\Manager;
use Manager\Domain\Model\Manager\Sales;
use Manager\Domain\Task\Sales\ViewAllSales;
use Manager\Domain\Task\Sales\ViewSalesList;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: Sales::class)]
class SalesController extends BaseController
{

    private function repository(): DoctrineSalesRepository
    {
        return $this->em->getRepository(Sales::class);
    }

    //
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function viewSalesList(Manager $manager, InputRequest $input)
    {
        $task = new ViewSalesList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $this->executeManagerTask($manager, $task, $payload);
        return $payload->result;
    }
    
    #[Query(responseWrapper: Query::LIST_RESPONSE_WRAPPER)]
    public function viewAllSales(Manager $manager, InputRequest $input)
    {
        $task = new ViewAllSales($this->repository());
        $payload = $this->buildViewAllListPayload($input);

        $this->executeManagerTask($manager, $task, $payload);
        return $payload->result;
    }

    #[Query]
    public function viewSalesDetail(Manager $manager, string $id)
    {
        $task = new \Manager\Domain\Task\Sales\ViewSalesDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $this->executeManagerTask($manager, $task, $payload);
        return $payload->result;
    }
}
