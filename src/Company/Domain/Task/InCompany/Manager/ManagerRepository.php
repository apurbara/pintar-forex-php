<?php

namespace Company\Domain\Task\InCompany\Manager;

use Company\Domain\Model\Personnel\Manager;

interface ManagerRepository
{

    public function nextIdentity(): string;

    public function add(Manager $manager): void;

    public function ofId(string $id): Manager;

    public function managerList(array $paginationSchema): array;

    public function managerDetail(string $id): array;
}
