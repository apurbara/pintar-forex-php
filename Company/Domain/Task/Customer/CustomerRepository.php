<?php

namespace Company\Domain\Task\Customer;

use Company\Domain\Model\Customer;

interface CustomerRepository
{

    public function nextIdentity(): string;

    public function add(Customer $customer): void;

    public function ofId(string $id): Customer;

    public function isPhoneAvailable(string $phone): bool;

    public function customerList(array $paginationSchema): array;

    public function allCustomer(array $searchSchema): array;

    public function aCustomer(string $id): array;
}
