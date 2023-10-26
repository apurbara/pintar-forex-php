<?php

namespace Tests\Http\Record\Model;

use Company\Domain\Model\Personnel;
use Tests\Http\Record\EntityRecord;
use Tests\Http\Record\JwtHeaderTokenGenerator;
use Tests\Http\Record\TestablePassword;

class PersonnelRecord extends EntityRecord
{

    public $rawPassword = 'password123';
    public $token;

    public function __construct($index)
    {
        parent::__construct(Personnel::class, $index);
        $this->columns['password'] = TestablePassword::getHashedPassword($this->rawPassword);
        $this->token = JwtHeaderTokenGenerator::generate([
                'userRole' => 'personnel',
                'userId' => $this->columns['id'],
        ]);
    }
}
