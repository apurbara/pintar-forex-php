<?php

namespace App\Http\Controllers\ManagerBC;

use App\Http\Controllers\Controller;
use Manager\Domain\Model\Personnel\Manager\Sales;
use Manager\Domain\Task\Sales\ViewSalesDetail;
use Manager\Domain\Task\Sales\ViewSalesList;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Query;

#[GraphqlMapableController(entity: Sales::class)]
class SalesController extends Controller
{

    protected function repository(): DoctrineSalesRepository
    {
        return $this->em->getRepository(Sales::class);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function salesList(ManagerRoleInterface $user, InputRequest $input)
    {
        $task = new ViewSalesList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeManagerTask($task, $payload);

        return $payload->result;
    }

    #[Query]
    public function salesDetail(ManagerRoleInterface $user, string $id)
    {
        $task = new ViewSalesDetail($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeManagerTask($task, $payload);

        return $payload->result;
    }
}
