<?php

namespace Tests\Sales\Application\Controllers;

use Company\Domain\Model\Manager\Sales;
use Shared\Application\UserRole;
use Tests\Http\Record\EntityRecord;
use Tests\Http\Record\JwtHeaderTokenGenerator;
use Tests\Http\Record\TestablePassword;

class SalesRecord extends EntityRecord
{

    public $rawPassword = 'password123';
    public $token;

    public function __construct($index)
    {
        parent::__construct(Sales::class, $index);
        $this->columns['password'] = TestablePassword::getHashedPassword($this->rawPassword);
        $this->columns['email'] = 'sales@email.org';
        $this->token = JwtHeaderTokenGenerator::generate([
                    'userRole' => UserRole::SALES->value,
                    'userId' => $this->columns['id'],
        ]);
    }
}
