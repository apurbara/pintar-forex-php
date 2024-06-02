<?php

namespace User\Domain\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Resources\Exception\RegularException;
use SharedContext\Domain\ValueObject\AccountInfo;
use SharedContext\Domain\ValueObject\ChangeUserPasswordData;
use User\Domain\Task\BySales\SalesTask;
use User\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;

#[Entity(repositoryClass: DoctrineSalesRepository::class)]
class Sales
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $cancelled;

    #[Embedded(class: AccountInfo::class, columnPrefix: false)]
    protected AccountInfo $accountInfo;

    public function getId(): string
    {
        return $this->id;
    }

    protected function __construct()
    {
        
    }

    public function changeName(string $name): void
    {
        $this->accountInfo = $this->accountInfo->changeName($name);
    }
    public function changePassword(ChangeUserPasswordData $changeUserPasswordData): void
    {
        $this->accountInfo = $this->accountInfo->changePassword($changeUserPasswordData);
    }

    //
    public function login(string $password): string
    {
        if ($this->cancelled || !$this->accountInfo->passwordMatch($password)) {
            throw RegularException::unauthorized('inactive account or invalid email and password');
        }
        return $this->id;
    }

    //
    public function executeTask(SalesTask $task, $payload): void
    {
        if ($this->cancelled) {
            throw RegularException::forbidden('only active personnel can make this request');
        }
        $task->executeBySales($this, $payload);
    }
}
