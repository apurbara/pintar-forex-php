<?php

namespace Sales\Domain\Task\CustomerVerification;

use Sales\Domain\Model\CustomerVerification;

interface CustomerVerificationRepository
{

    public function ofId(string $id): CustomerVerification;
}
