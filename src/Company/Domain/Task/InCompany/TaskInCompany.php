<?php

namespace Company\Domain\Task\InCompany;

interface TaskInCompany
{
    public function executeInCompany($payload): void;
}
