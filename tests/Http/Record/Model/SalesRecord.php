<?php

namespace Tests\Http\Record\Model;

use App\Http\Controllers\UserRole;
use Company\Domain\Model\Sales;
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
        $this->token = JwtHeaderTokenGenerator::generate([
                    'userRole' => UserRole::SALES->value,
                    'userId' => $this->columns['id'],
        ]);
    }
}
