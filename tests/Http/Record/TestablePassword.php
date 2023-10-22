<?php

namespace Tests\Http\Record;

use Resources\ValidationRule;
use Resources\ValidationService;

class TestablePassword
{
    public static function getHashedPassword(string $password)
    {
        $errorDetail = "bad request: password required at least 8 characters long contain alphabet and number";
//         $regex = "/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/";//no whitespace
        $regex = "/^(?=.*[A-Za-z ])(?=.*\d)[A-Za-z \d]{8,}$/"; //whitespace allowed
        ValidationService::build()
                ->addRule(ValidationRule::regex($regex))
                ->execute($password, $errorDetail);
        $options = [
            'cost' => 10
        ];
        return password_hash($password, PASSWORD_DEFAULT, $options);
    }
}
