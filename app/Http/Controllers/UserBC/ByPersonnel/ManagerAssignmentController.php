<?php

namespace App\Http\Controllers\UserBC\ByPersonnel;

use App\Http\Controllers\Controller;
use Resources\Application\InputRequest;
use User\Domain\Model\Personnel\Manager;
use User\Domain\Task\ByPersonnel\Manager\ViewManagerAssignmentList;
use User\Infrastructure\Persistence\Doctrine\Repository\DoctrineManagerRepository;

class ManagerAssignmentController extends Controller
{

    protected function repository(): DoctrineManagerRepository
    {
        return $this->em->getRepository(Manager::class);
    }

    //
    public function viewList(PersonnelRoleInterface $user, InputRequest $input)
    {
        $task = new ViewManagerAssignmentList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);
        $user->executePersonnelTask($task, $payload);

        return $payload->result;
    }
}
