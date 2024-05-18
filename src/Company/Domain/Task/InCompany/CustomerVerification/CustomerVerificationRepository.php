<?php

namespace Company\Domain\Task\InCompany\CustomerVerification;

use Company\Domain\Model\CustomerVerification;

interface CustomerVerificationRepository
{

    public function nextIdentity(): string;

    public function add(CustomerVerification $customerVerification): void;

    public function ofId(string $id): CustomerVerification;

    //
    public function customerVerificationList(array $paginationSchema): array;

    public function customerVerificationDetail(string $id): array;
}
