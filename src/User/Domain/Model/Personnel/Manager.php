<?php

namespace User\Domain\Model\Personnel;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Attributes\FetchableEntity;
use User\Domain\Model\Personnel;
use User\Infrastructure\Persistence\Doctrine\Repository\DoctrineManagerRepository;

#[Entity(repositoryClass: DoctrineManagerRepository::class)]
class Manager
{

    #[FetchableEntity(targetEntity: Personnel::class, joinColumnName: "Personnel_id")]
    #[ManyToOne(targetEntity: Personnel::class)]
    #[JoinColumn(name: "Personnel_id", referencedColumnName: "id")]
    protected Personnel $personnel;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    protected function __construct()
    {
        
    }
}
