<?php

namespace Sales\Domain\Model;

class SalesActivity
{
    protected string $id;
    protected bool $disabled;
    protected int $duration;
    protected bool $initial;
    
    protected function __construct()
    {
    }
}
