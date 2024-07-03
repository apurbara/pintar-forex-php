<?php

namespace Tests\Shared\Application;

use Company\Domain\Model\Manager\Sales;
use Shared\Application\UserRole;
use Tests\resources\Application\EntityRecord;

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
