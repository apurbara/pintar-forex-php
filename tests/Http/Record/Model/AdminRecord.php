<?php

namespace Tests\Http\Record\Model;

use Company\Domain\Model\Admin;
use Tests\Http\Record\EntityRecord;
use Tests\Http\Record\JwtHeaderTokenGenerator;
use Tests\Http\Record\TestablePassword;

class AdminRecord extends EntityRecord
{
    public $rawPassword = 'password123';
    public $token;
    
    public function __construct($index)
    {
        parent::__construct(Admin::class, $index);
        $this->columns['aSuperUser'] = true;
        $this->columns['password'] = TestablePassword::getHashedPassword($this->rawPassword);
        $this->token = JwtHeaderTokenGenerator::generate([
            'userRole' => 'admin',
            'userId' => $this->columns['id'],
        ]);
    }
    
}
