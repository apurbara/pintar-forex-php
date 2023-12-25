<?php

namespace SharedContext\Domain\ValueObject;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Resources\DateTimeImmutableBuilder;
use Resources\Event\ContainEventsTrait;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\ExcludeFromFetch;
use Resources\Infrastructure\GraphQL\Attributes\ExcludeFromInput;
use Resources\ValidationRule;
use Resources\ValidationService;
use SharedContext\Domain\Event\ResetUserPasswordFailed;

#[Embeddable]
class AccountInfo
{

    use ContainEventsTrait;
    
    #[Column(type: "string", length: 255, nullable: false)]
    protected string $name;

    #[Column(type: "string", length: 255, nullable: false)]
    protected string $email;

    #[ExcludeFromFetch]
    #[Column(type: "string", length: 60, nullable: true), ExcludeFromFetch]
    protected ?string $password;

    #[ExcludeFromFetch, ExcludeFromInput]
    #[Column(type: "string", length: 64, nullable: true), ExcludeFromFetch]
    protected ?string $resetPasswordToken;

    #[ExcludeFromFetch, ExcludeFromInput]
    #[Column(type: "datetimetz_immutable", nullable: true), ExcludeFromFetch]
    protected ?DateTimeImmutable $resetPasswordTokenExpiredTime;

   
    public function setName(string $name): void
    {
        ValidationService::build()
                ->addRule(ValidationRule::notEmpty())
                ->execute($name, 'user name is mandatory');
        $this->name = $name;
    }

    public function setEmail(string $email): void
    {
        ValidationService::build()
                ->addRule(ValidationRule::email())
                ->execute($email, 'user email must be valid email address');
        $this->email = $email;
    }

    public function setPassword(?string $password): void
    {
        $regex = "/^(?=.*[A-Za-z])(?=.*\d).{8,}$/"; //min lenght = 8; mandatory: alphabet, number; all character allowed
        $errorDetail = "minimum password length is 8 character and must contain combination of alphabet and number";
        ValidationService::build()
                ->addRule(ValidationRule::regex($regex))
                ->execute($password, $errorDetail);
        $options = [
            'cost' => 10
        ];
        $this->password = password_hash($password, PASSWORD_DEFAULT, $options);
    }

    public function __construct(AccountInfoData $data)
    {
        $this->setName($data->name);
        $this->setEmail($data->email);
        $this->setPassword($data->password);
        $this->resetPasswordToken = null;
        $this->resetPasswordTokenExpiredTime = null;
    }

    public function passwordMatch(string $password): bool
    {
        if (empty($this->password)) {
            throw RegularException::forbidden('you have not set password for this account, try signin using google account');
        }
        return password_verify($password, $this->password);
    }

    public function changeName(string $name): self
    {
        $user = clone $this;
        $user->setName($name);
        return $user;
    }

    public function changePassword(ChangeUserPasswordData $changePasswordData): self
    {
        if (!$this->passwordMatch($changePasswordData->previousPassword)) {
            $errorDetail = "forbidden: previous password not match";
            throw RegularException::forbidden($errorDetail);
        }
        $user = clone $this;
        $user->setPassword($changePasswordData->newPassword);
        return $user;
    }

    public function generateResetPasswordToken(): self
    {
        $userPassword = clone $this;
        $userPassword->resetPasswordToken = bin2hex(random_bytes(32));
        $userPassword->resetPasswordTokenExpiredTime = DateTimeImmutableBuilder::buildYmdHisAccuracy('+10 minutes');
        return $userPassword;
    }

    public function resetPassword(string $token, string $password): self
    {
        $user = clone $this;
        if (!empty($token) && $token === $this->resetPasswordToken && $this->resetPasswordTokenExpiredTime > new DateTimeImmutable()
        ) {
            $user->setPassword($password);
        } else {
            $event = new ResetUserPasswordFailed();
            $user->recordEvent($event);
        }
        $user->resetPasswordToken = null;
        $user->resetPasswordTokenExpiredTime = null;
        return $user;
    }
}
