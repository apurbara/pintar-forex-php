<?php

namespace SharedContext\Domain\ValueObject;

use DateTimeImmutable;
use Resources\DateTimeImmutableBuilder;
use SharedContext\Domain\Event\ResetUserPasswordFailed;
use Tests\TestBase;

class AccountInfoTest extends TestBase
{

    protected $accountInfo, $resetPasswordToken = 'resetPasswordToken';
    protected $name = 'new name', $email = 'newAddress@email.org', $password = 'password123';
    protected $newPassword = "newPassword123";

    protected function setUp(): void
    {
        parent::setUp();
        $this->accountInfo = new TestableUser(new AccountInfoData('name', 'address@email.org', $this->password));
        $this->accountInfo->resetPasswordToken = $this->resetPasswordToken;
        $this->accountInfo->resetPasswordTokenExpiredTime = new DateTimeImmutable('+24 hours');
    }

    //
    protected function createAccountInfoData()
    {
        return new AccountInfoData($this->name, $this->email, $this->password);
    }

    //
    protected function construct()
    {
        return new TestableUser($this->createAccountInfoData());
    }

    public function test_construct_setProperties()
    {
        $accountInfo = $this->construct();
        $this->assertSame($this->name, $accountInfo->name);
        $this->assertSame($this->email, $accountInfo->email);
        $this->assertTrue(password_verify($this->password, $accountInfo->password));
        $this->assertNull($accountInfo->resetPasswordToken);
        $this->assertNull($accountInfo->resetPasswordTokenExpiredTime);
    }

    public function test_construct_emptyName_badRequest()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(function () {
            $this->construct();
        }, 'Bad Request', 'user name is mandatory');
    }

    public function test_construct_invalidEmail_badRequest()
    {
        $this->email = 'invalid mail format';
        $this->assertRegularExceptionThrowed(function () {
            $this->construct();
        }, 'Bad Request', 'user email must be valid email address');
    }

    public function test_construct_invalidPasswordFormat_lessThanEightCharacter_badRequest()
    {
        $this->password = '5Hort';
        $this->assertRegularExceptionThrowed(function () {
            $this->construct();
        }, 'Bad Request', 'minimum password length is 8 character and must contain combination of alphabet and number');
    }

    public function test_construct_invalidPasswordFormat_noNumber_badRequest()
    {
        $this->password = 'noNumber';
        $this->assertRegularExceptionThrowed(function () {
            $this->construct();
        }, 'Bad Request', 'minimum password length is 8 character and must contain combination of alphabet and number');
    }

    public function test_construct_invalidPasswordFormat_noAlphabet_badRequest()
    {
        $this->password = '213123131231242134*&^*&';
        $this->assertRegularExceptionThrowed(function () {
            $this->construct();
        }, 'Bad Request', 'minimum password length is 8 character and must contain combination of alphabet and number');
    }

    public function test_construct_emptyPassword_badRequest()
    {
        $this->password = null;
        $this->assertRegularExceptionThrowed(function () {
            $this->construct();
        }, 'Bad Request', 'minimum password length is 8 character and must contain combination of alphabet and number');
    }

    //
    protected function passwordMatch()
    {
        return $this->accountInfo->passwordMatch($this->password);
    }

    public function test_passwordMatch_matchPassword_returnTrue()
    {
        $this->assertTrue($this->passwordMatch());
    }

    public function test_passwordMatch_unmatchPassword_returnFalse()
    {
        $this->password = 'differentPassword';
        $this->assertFalse($this->passwordMatch());
    }

    public function test_passwordMatch_nonExistingPassword_forbidden()
    {
        $this->accountInfo->password = null;
        $this->assertRegularExceptionThrowed(fn() => $this->passwordMatch(), 'Forbidden',
                'you have not set password for this account, try signin using google account');
    }

    //
    protected function changeName()
    {
        return $this->accountInfo->changeName($this->name);
    }

    public function test_changeName_changeName()
    {
        $accountInfo = $this->changeName();
        $this->assertSame($this->name, $accountInfo->name);

        $this->assertEquals($accountInfo->email, $this->accountInfo->email);
        $this->assertEquals($accountInfo->password, $this->accountInfo->password);
        $this->assertEquals($accountInfo->resetPasswordToken, $this->accountInfo->resetPasswordToken);
        $this->assertEquals($accountInfo->resetPasswordTokenExpiredTime,
                $this->accountInfo->resetPasswordTokenExpiredTime);
    }

    public function test_changeName_emptyName_badRequest()
    {
        $this->name = '';
        $this->assertRegularExceptionThrowed(function () {
            $this->changeName();
        }, 'Bad Request', 'user name is mandatory');
    }

    //
    protected function getChangePasswordData()
    {
        return new ChangeUserPasswordData($this->password, $this->newPassword);
    }

    protected function changePassword()
    {
        return $this->accountInfo->changePassword($this->getChangePasswordData());
    }

    public function test_changePassword_changePassword()
    {
        $accountInfo = $this->changePassword();
        $this->assertTrue(password_verify($this->newPassword, $accountInfo->password));

        $this->assertEquals($accountInfo->name, $this->accountInfo->name);
        $this->assertEquals($accountInfo->email, $this->accountInfo->email);
        $this->assertEquals($accountInfo->resetPasswordToken, $this->accountInfo->resetPasswordToken);
        $this->assertEquals($accountInfo->resetPasswordTokenExpiredTime,
                $this->accountInfo->resetPasswordTokenExpiredTime);
    }

    public function test_changePassword_unamtchedPreviousPassword_forbidden()
    {
        $this->password = "unamatchedPassword123";
        $operation = function () {
            $this->changePassword();
        };
        $errorDetail = "forbidden: previous password not match";
        $this->assertRegularExceptionThrowed($operation, "Forbidden", $errorDetail);
    }

    protected function generateResetPasswordToken()
    {
        return $this->accountInfo->generateResetPasswordToken();
    }

    public function test_generateResetPasswordToken_setTokenAndExpiredTime()
    {
        $this->accountInfo->resetPasswordToken = null;
        $this->accountInfo->resetPasswordTokenExpiredTime = null;

        $accountInfo = $this->generateResetPasswordToken();
        $this->assertNotEmpty($accountInfo->resetPasswordToken);
        $this->assertEquals(DateTimeImmutableBuilder::buildYmdHisAccuracy('+10 minutes'),
                $accountInfo->resetPasswordTokenExpiredTime);

        $this->assertEquals($accountInfo->name, $this->accountInfo->name);
        $this->assertEquals($accountInfo->email, $this->accountInfo->email);
        $this->assertEquals($accountInfo->password, $this->accountInfo->password);
    }

    protected function resetPassword()
    {
        return $this->accountInfo->resetPassword($this->resetPasswordToken, $this->newPassword);
    }

    public function test_resetPassword_changePasswordAndClearToken()
    {
        $accountInfo = $this->resetPassword();
        $this->assertTrue(password_verify($this->newPassword, $accountInfo->password));
        $this->assertNull($accountInfo->resetPasswordToken);
        $this->assertNull($accountInfo->resetPasswordTokenExpiredTime);

        $this->assertEquals($accountInfo->name, $this->accountInfo->name);
        $this->assertEquals($accountInfo->email, $this->accountInfo->email);
        $this->assertEmpty($accountInfo->recordedEvents());
    }

    public function test_resetPassword_unmatchToken_recordFailedEvent()
    {
        $this->resetPasswordToken = 'unmatchedToken';
        $accountInfo = $this->resetPassword();
        $event = new ResetUserPasswordFailed();
        $this->assertEquals($event, $accountInfo->recordedEvents()[0]);
        $this->assertEquals($accountInfo->password, $this->accountInfo->password);
    }

    public function test_resetPassword_expiredToken_recordFailedEvent()
    {
        $this->accountInfo->resetPasswordTokenExpiredTime = new \DateTimeImmutable('-1 hours');
        $accountInfo = $this->resetPassword();

        $event = new ResetUserPasswordFailed();
        $this->assertEquals($event, $accountInfo->recordedEvents()[0]);
        $this->assertEquals($accountInfo->password, $this->accountInfo->password);
    }

    public function test_resetPassword_emptyToken_recordFailedEvent()
    {
        $this->accountInfo->resetPasswordToken = null;
        $accountInfo = $this->resetPassword();

        $event = new ResetUserPasswordFailed();
        $this->assertEquals($event, $accountInfo->recordedEvents()[0]);
        $this->assertEquals($accountInfo->password, $this->accountInfo->password);
    }
}

class TestableUser extends AccountInfo
{

    public string $name;
    public string $email;
    public ?string $password;
    public ?string $resetPasswordToken;
    public ?DateTimeImmutable $resetPasswordTokenExpiredTime;

    public function recordedEvents()
    {
        return $this->recordedEvents;
    }
}
