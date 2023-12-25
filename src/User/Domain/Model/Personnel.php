<?php

namespace User\Domain\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use SharedContext\Domain\ValueObject\AccountInfo;
use User\Domain\Model\Personnel\Manager;
use User\Domain\Model\Personnel\Sales;
use User\Domain\Task\ByPersonnel\PersonnelTask;
use User\Infrastructure\Persistence\Doctrine\Repository\DoctrinePersonnelRepository;

#[Entity(repositoryClass: DoctrinePersonnelRepository::class)]
class Personnel
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Embedded(class: AccountInfo::class, columnPrefix: false)]
    protected AccountInfo $accountInfo;

    //
    #[FetchableObjectList(targetEntity: Sales::class, joinColumnName: "Personnel_id", paginationRequired: false)]
    protected $salesAssignments;

    #[FetchableObjectList(targetEntity: Manager::class, joinColumnName: "Personnel_id", paginationRequired: false)]
    protected $managerAssignments;

    public function getId(): string
    {
        return $this->id;
    }

    protected function __construct()
    {
        
    }

    //
    public function login(string $password): string
    {
        if ($this->disabled || !$this->accountInfo->passwordMatch($password)) {
            throw RegularException::unauthorized('inactive account or invalid email and password');
        }
        return $this->id;
    }

    //
    public function executeTask(PersonnelTask $task, $payload): void
    {
        if ($this->disabled) {
            throw RegularException::forbidden('only active personnel can make this request');
        }
        $task->executeByPersonnel($this, $payload);
    }
}
