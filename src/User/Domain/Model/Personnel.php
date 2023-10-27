<?php

namespace User\Domain\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Resources\Exception\RegularException;
use SharedContext\Domain\ValueObject\AccountInfo;
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
}
