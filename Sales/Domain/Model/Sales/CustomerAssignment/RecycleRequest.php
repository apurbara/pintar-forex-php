<?php

namespace Sales\Domain\Model\Sales\CustomerAssignment;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\CustomerAssignment;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineRecycleRequestRepository;
use Shared\Domain\Enum\ManagementApprovalStatus;

#[Entity(repositoryClass: DoctrineRecycleRequestRepository::class)]
class RecycleRequest
{

    #[FetchableObject(targetEntity: CustomerAssignment::class, joinColumnName: "CustomerAssignment_id")]
    #[ManyToOne(targetEntity: CustomerAssignment::class, inversedBy: "recycleReports", fetch: "LAZY")]
    #[JoinColumn(name: "CustomerAssignment_id", referencedColumnName: "id")]
    protected CustomerAssignment $customerAssignment;
    
    #[Id, Column(type: "guid")]
    protected string $id;
    
    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;
    
    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $concludedTime;
    
    #[Column(type: "string", enumType: ManagementApprovalStatus::class)]
    protected ManagementApprovalStatus $status;
    
    #[Column(type: "text", nullable: true)]
    protected ?string $note;
    
    #[Column(type: "text", nullable: true)]
    protected ?string $remark;

    public function __construct(CustomerAssignment $customerAssignment, RecycleRequestData $data)
    {
        $this->customerAssignment = $customerAssignment;
        $this->id = $data->id;
        $this->createdTime = new \DateTimeImmutable();
        $this->status = ManagementApprovalStatus::WAITING_FOR_APPROVAL;
        $this->note = $data->note;
    }

    public function update(RecycleRequestData $data): void
    {
        if ($this->status !== ManagementApprovalStatus::WAITING_FOR_APPROVAL) {
            throw RegularException::forbidden('request already concluded');
        }
        $this->note = $data->note;
    }

    //
    public function assertManageableBySales(Sales $sales): void
    {
        $this->customerAssignment->assertBelongsToSales($sales);
    }
    
    //
    public function isOngoing(): bool
    {
        return $this->status === ManagementApprovalStatus::WAITING_FOR_APPROVAL;
    }
}
