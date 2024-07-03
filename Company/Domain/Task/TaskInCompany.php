<?php

namespace Company\Domain\Task;

interface TaskInCompany
{
    public function executeInCompany($payload): void;
}
