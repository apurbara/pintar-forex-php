<?php

namespace App\Http\Controllers\UserBC\ByPersonnel;

use App\Http\Controllers\Controller;
use Resources\Application\InputRequest;
use User\Domain\Model\Personnel\Sales;
use User\Domain\Task\ByPersonnel\Sales\ViewSalesAssignmentList;
use User\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;

class SalesAssignmentController extends Controller
{

    protected function repository(): DoctrineSalesRepository
    {
        return $this->em->getRepository(Sales::class);
    }

    //
    public function viewList(PersonnelRoleInterface $user, InputRequest $input)
    {
        $task = new ViewSalesAssignmentList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executePersonnelTask($task, $payload);
        
        return $payload->result;
    }
}
