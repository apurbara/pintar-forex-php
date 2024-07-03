<?php

namespace Tests\Shared\Application;

use Company\Domain\Model\Admin;
use Shared\Application\UserRole;
use Tests\resources\Application\EntityRecord;

class AdminRecord extends EntityRecord
{

    public $rawPassword = 'password123';
    public $token;

    public function __construct($index)
    {
        parent::__construct(Admin::class, $index);
        $this->columns['aSuperUser'] = true;
        $this->columns['email'] = 'admin@email.org';
        $this->columns['password'] = TestablePassword::getHashedPassword($this->rawPassword);
        $this->token = JwtHeaderTokenGenerator::generate([
                    'userRole' => UserRole::ADMIN->value,
                    'userId' => $this->columns['id'],
        ]);
    }
}
