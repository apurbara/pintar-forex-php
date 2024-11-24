<?php

namespace Manager\Domain\DependencyModel;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Manager\Domain\Model\Manager\Sales\FactFindingAssignment;
use Manager\Domain\Model\Manager\Sales\GreetingAssignment;
use Manager\Domain\Model\Manager\Sales\StrikingAssignment;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerRepository;
use Resources\Exception\RegularException;
use Shared\Domain\Enum\CustomerAssignmentStatus;
use Shared\Domain\Enum\CustomerStatus;

#[Entity(repositoryClass: DoctrineCustomerRepository::class)]
class Customer
{

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "string", enumType: CustomerStatus::class, options: ["default" => CustomerStatus::NEW->value])]
    protected CustomerStatus $status;

    #[OneToMany(targetEntity: GreetingAssignment::class, mappedBy: "customer", fetch: "EXTRA_LAZY")]
    protected Collection $greetingAssignments;

    #[OneToMany(targetEntity: FactFindingAssignment::class, mappedBy: "customer", fetch: "EXTRA_LAZY")]
    protected Collection $factFindingAssignments;

    #[OneToMany(targetEntity: StrikingAssignment::class, mappedBy: "customer", fetch: "EXTRA_LAZY")]
    protected Collection $strikingAssignments;

    public function getId(): string
    {
        return $this->id;
    }

    //
    protected function __construct()
    {
        
    }

    //
    public function assertHasNoActiveAssignment(): void
    {
        $criteria = Criteria::create()
                ->andWhere(Criteria::expr()->eq('status', CustomerAssignmentStatus::ACTIVE));
        $hasActiveAssignment = !$this->greetingAssignments->matching($criteria)->isEmpty() 
                || !$this->factFindingAssignments->matching($criteria)->isEmpty()
                || !$this->strikingAssignments->matching($criteria)->isEmpty();
        if ($hasActiveAssignment) {
            throw RegularException::forbidden('customer already being maintained');
        }
    }

    public function assertStatusEquals(CustomerStatus $status): void
    {
        if ($this->status !== $status) {
            throw RegularException::forbidden('unmatch customer status');
        }
    }
}
