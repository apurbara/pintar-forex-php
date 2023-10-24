<?php

namespace Company\Domain\Task\InCompany\Personnel;

use Company\Domain\Model\Personnel;

interface PersonnelRepository
{

    public function nextIdentity(): string;

    public function add(Personnel $personnel): void;
    
    public function ofId(string $id): Personnel;
    
    public function isEmailAvailable(string $email): bool;

    public function viewPersonnelList(array $paginationSchema): array;

    public function viewPersonnelDetail(string $id): array;
}
