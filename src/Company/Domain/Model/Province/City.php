<?php

namespace Company\Domain\Model\Province;

use Company\Domain\Model\Province;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCityRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\ValidationRule;
use Resources\ValidationService;

#[Entity(repositoryClass: DoctrineCityRepository::class)]
class City
{

    #[FetchableObject(targetEntity: Province::class, joinColumnName: "Province_id")]
    #[ManyToOne(targetEntity: Province::class, fetch: "EXTRA_LAZY")]
    #[JoinColumn(name: "Province_id", referencedColumnName: "id")]
    protected Province $province;

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
                ->execute($name, 'city name is mandatory');
        $this->name = $name;
    }

    public function __construct(Province $province, string $id, CityData $data)
    {
        $this->province = $province;
        $this->id = $id;
        $this->createdTime = new \DateTimeImmutable();
        $this->disabled = false;
        $this->setName($data->name);

        //
        $this->province->assertActive();
    }

    public function update(Province $province, CityData $data): void
    {
        $this->province = $province;
        $this->setName($data->name);

        //
        $this->province->assertActive();
    }

    public function disable(): void
    {
        $this->disabled = true;
    }

    public function enable(): void
    {
        $this->disabled = false;
    }
}
