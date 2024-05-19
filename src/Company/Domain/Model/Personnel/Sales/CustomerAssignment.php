<?php

namespace Company\Domain\Model\Personnel\Sales;

use Company\Domain\Model\Personnel\Sales;
use DateTimeImmutable;

class CustomerAssignment
{

    protected Sales $sales;
    protected string $id;
    protected bool $cancelled;
    protected DateTimeImmutable $createdTime;

    public function isCancelled(): bool
    {
        return $this->cancelled;
    }
    
    protected function __construct()
    {
        
    }

    //
    public function cancel(): void
    {
        
    }
}
