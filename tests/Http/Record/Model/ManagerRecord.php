<?php

namespace Tests\Http\Record\Model;

use App\Http\Controllers\UserRole;
use Company\Domain\Model\Manager;
use Tests\Http\Record\EntityRecord;
use Tests\Http\Record\JwtHeaderTokenGenerator;
use Tests\Http\Record\TestablePassword;

class ManagerRecord extends EntityRecord
{
    public $rawPassword = 'password123';
    public $token;
    
    public function __construct($index)
    {
        parent::__construct(Manager::class, $index);
        $this->columns['password'] = TestablePassword::getHashedPassword($this->rawPassword);
        $this->columns['email'] = 'manager@email.org';
        $this->token = JwtHeaderTokenGenerator::generate([
            'userRole' => UserRole::MANAGER->value,
            'userId' => $this->columns['id'],
        ]);
    }
    
}
