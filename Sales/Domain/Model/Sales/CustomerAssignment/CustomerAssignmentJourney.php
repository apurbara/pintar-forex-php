<?php

namespace Sales\Domain\Model\Sales\CustomerAssignment;

use Company\Domain\Model\CustomerJourney as CustomerJourney2;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Sales\Domain\DependencyModel\CustomerJourney;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerAssignmentJourneyRepository;

#[Entity(repositoryClass: DoctrineCustomerAssignmentJourneyRepository::class)]
class CustomerAssignmentJourney
{

    #[FetchableObject(targetEntity: CustomerAssignment::class, joinColumnName: "CustomerAssignment_id")]
    #[ManyToOne(targetEntity: CustomerAssignment::class, inversedBy: "customerAssignmentJourneys", fetch: "LAZY")]
    #[JoinColumn(name: "CustomerAssignment_id", referencedColumnName: "id")]
    protected CustomerAssignment $customerAssignment;

    #[FetchableObject(targetEntity: CustomerJourney2::class, joinColumnName: "CustomerJourney_id")]
    #[JoinColumn(name: "CustomerJourney_id", referencedColumnName: "id")]
    #[ManyToOne(targetEntity: CustomerJourney::class, fetch: "EXTRA_LAZY")]
    protected CustomerJourney $customerJourney;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: false, options: ["default" => "CURRENT_TIMESTAMP"])]
    protected DateTimeImmutable $startTime;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected ?DateTimeImmutable $endTime;

    public function __construct(CustomerAssignment $customerAssignment, CustomerJourney $customerJourney, string $id)
    {
        $this->customerAssignment = $customerAssignment;
        $this->customerJourney = $customerJourney;
        $this->id = $id;
        $this->startTime = new \DateTimeImmutable();
    }
    
    public function completeJourney(): void
    {
        $this->endTime = new \DateTimeImmutable();
    }
    
    //
    public function ongoingJourneyAssociateWith(CustomerJourney $customerJourney): bool
    {
        return $this->customerJourney === $customerJourney;
    }
}
