<?php

namespace Company\Domain\Model;

use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineProvinceRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Resources\Exception\RegularException;
use Resources\ValidationRule;
use Resources\ValidationService;

#[Entity(repositoryClass: DoctrineProvinceRepository::class)]
class Province
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: false, options: ["default" => "CURRENT_TIMESTAMP"])]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    #[Column(type: "string", length: 255, nullable: false)]
    protected string $name;

    protected function setName(string $name)
    {
        ValidationService::build()
                ->addRule(ValidationRule::notEmpty())
                ->execute($name, 'province name is mandatory');
        $this->name = $name;
    }

    public function __construct(string $id, ProvinceData $data)
    {
        $this->id = $id;
        $this->createdTime = new \DateTimeImmutable();
        $this->disabled = false;
        $this->setName($data->name);
    }

    public function update(ProvinceData $data): void
    {
        $this->setName($data->name);
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
            throw RegularException::forbidden('inactive province');
        }
    }
}
