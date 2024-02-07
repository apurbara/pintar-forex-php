<?php

namespace Company\Domain\Task\InCompany\Customer;

interface CustomerRepository
{
    public function customerList(array $paginationSchema): array;
}
