<?php

namespace Tests\Manager\Application\Controllers;

use Manager\Domain\Model\Manager;
use Shared\Application\UserRole;
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
