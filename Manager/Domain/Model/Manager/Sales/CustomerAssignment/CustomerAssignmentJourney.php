<?php

namespace Manager\Domain\Model\Manager\Sales\CustomerAssignment;

use Company\Domain\Model\CustomerJourney as CustomerJourney2;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Manager\Domain\DependencyModel\CustomerJourney;
use Manager\Domain\Model\Manager\Sales\CustomerAssignment;
use Manager\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerAssignmentJourneyRepository;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;

#[Entity(repositoryClass: DoctrineCustomerAssignmentJourneyRepository::class)]
class CustomerAssignmentJourney
{

    #[ManyToOne(targetEntity: CustomerAssignment::class, inversedBy: "customerAssignmentJourneys", fetch: "LAZY")]
    #[JoinColumn(name: "CustomerAssignment_id", referencedColumnName: "id")]
    protected CustomerAssignment $customerAssignment;

    #[FetchableObject(targetEntity: CustomerJourney2::class, joinColumnName: "CustomerJourney_id")]
    #[ManyToOne(targetEntity: CustomerJourney::class, fetch: "EXTRA_LAZY")]
    #[JoinColumn(name: "CustomerJourney_id", referencedColumnName: "id")]
    protected CustomerJourney $customerJourney;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: false, options: ["default" => "CURRENT_TIMESTAMP"])]
    protected DateTimeImmutable $startTime;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected ?DateTimeImmutable $endTime;

    protected function __construct()
    {
        
    }
}
