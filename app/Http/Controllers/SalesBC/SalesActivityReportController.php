<?php

namespace App\Http\Controllers\SalesBC;

use App\Http\Controllers\Controller;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Mutation;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReport;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer\SalesActivitySchedule\SalesActivityReportData;
use Sales\Domain\Task\SalesActivityReport\SubmitSalesActivityReportTask;
use Sales\Domain\Task\SalesActivityReport\ViewSalesActivityReportDetailTask;
use Sales\Domain\Task\SalesActivityReport\ViewSalesActivityReportListTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesActivityReportRepository;

#[GraphqlMapableController(entity: SalesActivityReport::class)]
class SalesActivityReportController extends Controller
{

    protected function repository(): DoctrineSalesActivityReportRepository
    {
        return $this->em->getRepository(SalesActivityReport::class);
    }

    //
    #[Mutation]
    public function submitSalesActivityReport(SalesRoleInterface $user, string $SalesActivitySchedule_id, InputRequest $input)
    {
        $repository = $this->repository();
        $salesActivityScheduleRepository = $this->em->getRepository(SalesActivitySchedule::class);
        $task = new SubmitSalesActivityReportTask($repository, $salesActivityScheduleRepository);

        $payload = (new SalesActivityReportData($input->get('content')))
                ->setSalesActivityScheduleId($SalesActivitySchedule_id);

        $user->executeSalesTask($task, $payload);
        return $repository->queryOneById($payload->id);
    }

    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function salesActivityReportList(SalesRoleInterface $user, InputRequest $input)
    {
        $task = new ViewSalesActivityReportListTask($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executeSalesTask($task, $payload);

        return $payload->result;
    }

    #[Query]
    public function salesActivityReportDetail(SalesRoleInterface $user, string $id)
    {
        $task = new ViewSalesActivityReportDetailTask($this->repository());
        $payload = new ViewDetailPayload($id);
        $user->executeSalesTask($task, $payload);

        return $payload->result;
    }
}
