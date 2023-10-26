<?php

namespace Sales\Domain\Model\AreaStructure\Area;

use Company\Domain\Model\AreaStructure\Area as AreaInCompanyBC;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Attributes\FetchableEntity;
use Resources\ValidationRule;
use Resources\ValidationService;
use Sales\Domain\Model\AreaStructure\Area;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerRepository;

#[Entity(repositoryClass: DoctrineCustomerRepository::class)]
class Customer
{

    #[FetchableEntity(targetEntity: AreaInCompanyBC::class, joinColumnName: "Area_id")]
    #[ManyToOne(targetEntity: Area::class)]
    #[JoinColumn(name: "Area_id", referencedColumnName: "id")]
    protected Area $area;
    
    #[Id, Column(type: "guid")]
    protected string $id;
    
    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;
    
    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;
    
    #[Column(type: "string", length: 255, nullable: false)]
    protected string $name;
    
    #[Column(type: "string", length: 255, nullable: false)]
    protected string $email;

    protected function setName(string $name)
    {
        ValidationService::build()
                ->addRule(ValidationRule::notEmpty())
                ->execute($name, 'customer name is mandatory');
        $this->name = $name;
    }

    protected function setEmail(string $email)
    {
        ValidationService::build()
                ->addRule(ValidationRule::email())
                ->execute($email, 'customer email is mandatory and must be in valid email address format');
        $this->email = $email;
    }

    public function __construct(Area $area, CustomerData $data)
    {
        $this->id = $data->id;
        $this->disabled = false;
        $this->createdTime = new DateTimeImmutable();
        $this->setName($data->name);
        $this->setEmail($data->email);
        $this->area = $area;
    }
}
