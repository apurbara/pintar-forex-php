<?php

namespace Sales\Domain\Model;

use Company\Domain\Model\Manager as FetchableManagerFromCompanyBC;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Sales\Domain\Task\SalesTask;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;
use Shared\Domain\Enum\SalesRole;
use Shared\Domain\ValueObject\AccountInfo;
use Shared\Domain\ValueObject\ChangeUserPasswordData;

#[Entity(repositoryClass: DoctrineSalesRepository::class)]
class Sales
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $contractTerminated;

    #[Embedded(class: AccountInfo::class, columnPrefix: false)]
    protected AccountInfo $accountInfo;

    #[Column(type: "string", enumType: SalesRole::class)]
    protected SalesRole $role;

    //
    #[FetchableObject(targetEntity: FetchableManagerFromCompanyBC::class, joinColumnName: "Manager_id")]
    #[JoinColumn(name: "Manager_id", referencedColumnName: "id")]
    protected $manager;

    public function getId(): string
    {
        return $this->id;
    }

    public function getRole(): SalesRole
    {
        return $this->role;
    }

    protected function __construct()
    {
        
    }

    public function changePassword(ChangeUserPasswordData $changePasswordData): void
    {
        $this->accountInfo = $this->accountInfo->changePassword($changePasswordData);
    }

    public function changeName(string $name): void
    {
        $this->accountInfo = $this->accountInfo->changeName($name);
    }

    public function login(string $password): string
    {
        if ($this->contractTerminated || !$this->accountInfo->passwordMatch($password)) {
            throw RegularException::unauthorized('inactive account or invalid email and password');
        }
        return $this->id;
    }

    //
    public function assertActive(): void
    {
        if ($this->contractTerminated) {
            throw RegularException::forbidden('inactive sales');
        }
    }

    //
    public function executeTask(SalesTask $task, $payload): void
    {
        if ($this->contractTerminated) {
            throw RegularException::forbidden('only active sales can make this request');
        }
        $task->executeBySales($this, $payload);
    }
}
