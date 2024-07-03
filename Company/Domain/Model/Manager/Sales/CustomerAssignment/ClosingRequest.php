<?php

namespace Company\Domain\Model\Manager\Sales\CustomerAssignment;

use Company\Domain\Model\Manager\Sales\CustomerAssignment;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineClosingRequestRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Shared\Domain\Enum\ManagementApprovalStatus;

#[Entity(repositoryClass: DoctrineClosingRequestRepository::class)]
class ClosingRequest
{

    #[FetchableObject(targetEntity: CustomerAssignment::class, joinColumnName: "CustomerAssignment_id")]
    #[ManyToOne(targetEntity: CustomerAssignment::class, inversedBy: "closingRequests", fetch: "LAZY")]
    #[JoinColumn(name: "CustomerAssignment_id", referencedColumnName: "id")]
    protected CustomerAssignment $customerAssignment;

    #[Id, Column(type: "guid")]
    protected string $id;

    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;

    #[Column(type: "string", enumType: ManagementApprovalStatus::class)]
    protected ManagementApprovalStatus $status;

    #[Column(type: "integer", nullable: false)]
    protected int $transactionValue;

    #[Column(type: "text", nullable: true)]
    protected ?string $note;

    #[Column(type: "text", nullable: true)]
    protected ?string $remark;

    public function getStatus(): ManagementApprovalStatus
    {
        return $this->status;
    }

    protected function __construct()
    {
        
    }

    public function cancelBySystem(): void
    {
        $this->status = ManagementApprovalStatus::CANCELLED_BY_SYSTEM;
    }
}
