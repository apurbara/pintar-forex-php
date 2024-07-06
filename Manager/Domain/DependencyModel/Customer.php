<?php

namespace Manager\Domain\DependencyModel;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerRepository;
use Resources\Exception\RegularException;
use Shared\Domain\Enum\CustomerAssignmentStatus;

#[Entity(repositoryClass: DoctrineCustomerRepository::class)]
class Customer
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;

    #[OneToMany(targetEntity: CustomerAssignment::class, mappedBy: "customer", fetch: "EXTRA_LAZY")]
    protected Collection $customerAssignments;

    public function getId(): string
    {
        return $this->id;
    }

    //
    protected function __construct()
    {
        
    }

    //
    public function assertActive(): void
    {
        if ($this->disabled) {
            throw RegularException::forbidden('inactive customer');
        }
    }
    
    public function assertHasNoActiveAssignment(): void
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', CustomerAssignmentStatus::ACTIVE));
        if (!$this->customerAssignments->matching($criteria)->isEmpty()) {
            throw RegularException::forbidden('customer already being maintained');
        }
    }
}
