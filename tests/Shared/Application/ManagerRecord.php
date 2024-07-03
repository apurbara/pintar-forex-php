<?php

namespace Tests\Shared\Application;

use Company\Domain\Model\Manager;
use Shared\Application\UserRole;
use Tests\resources\Application\EntityRecord;

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
