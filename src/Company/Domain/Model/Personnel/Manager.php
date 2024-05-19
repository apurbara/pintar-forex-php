<?php

namespace Company\Domain\Model\Personnel;

use Company\Domain\Model\Personnel;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineManagerRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;

#[Entity(repositoryClass: DoctrineManagerRepository::class)]
class Manager
{

    #[FetchableObject(targetEntity: Personnel::class, joinColumnName: "Personnel_id")]
    #[ManyToOne(targetEntity: Personnel::class)]
    #[JoinColumn(name: "Personnel_id", referencedColumnName: "id")]
    protected Personnel $personnel;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function __construct(Personnel $personnel, ManagerData $data)
    {
        $this->personnel = $personnel;
        $this->id = $data->id;
        $this->createdTime = new DateTimeImmutable();
        $this->disabled = false;
    }
    
    public function disable(): void
    {
        $this->disabled = true;
    }
    
    public function enable(): void
    {
        $this->disabled = false;
    }

    //
    public function assertActive(): void
    {
        if ($this->disabled) {
            throw RegularException::forbidden('inactive manager');
        }
    }
}
