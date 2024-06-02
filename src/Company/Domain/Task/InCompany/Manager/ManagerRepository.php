<?php

namespace Company\Domain\Task\InCompany\Manager;

use Company\Domain\Model\Manager;

interface ManagerRepository
{

    public function nextIdentity(): string;

    public function add(Manager $manager): void;
    
    public function ofId(string $id): Manager;
    
    public function isEmailAvailable(string $email): bool;

    //
    public function viewManagerList(array $paginationSchema): array;

    public function viewManagerDetail(string $id): array;
}
