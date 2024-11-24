<?php

namespace Sales\Domain\Model\Sales\StrikingAssignment;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\ValidationRule;
use Resources\ValidationService;
use Sales\Domain\Model\Sales;
use Sales\Domain\Model\Sales\StrikingAssignment;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineClosingRequestRepository;
use Shared\Domain\Enum\ManagementApprovalStatus;

#[Entity(repositoryClass: DoctrineClosingRequestRepository::class)]
class ClosingRequest
{

    #[FetchableObject(targetEntity: StrikingAssignment::class, joinColumnName: "StrikingAssignment_id")]
    #[ManyToOne(targetEntity: StrikingAssignment::class, inversedBy: "closingRequests", fetch: "LAZY")]
    #[JoinColumn(name: "StrikingAssignment_id", referencedColumnName: "id")]
    protected StrikingAssignment $strikingAssignment;

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

    protected function setTransactionValue(int $transactionValue)
    {
        ValidationService::build()
                ->addRule(ValidationRule::notEmpty())
                ->execute($transactionValue, 'transaction value is mandatory');
        $this->transactionValue = $transactionValue;
    }
    
    public function __construct(StrikingAssignment $strikingAssignment, string $id, ClosingRequestData $data)
    {
        $this->strikingAssignment = $strikingAssignment;
        $this->id = $id;
        $this->createdTime = new \DateTimeImmutable();
        $this->status = ManagementApprovalStatus::WAITING_FOR_APPROVAL;
        $this->setTransactionValue($data->transactionValue);
        $this->note = $data->note;
    }

    public function update(ClosingRequestData $data): void
    {
        if ($this->status !== ManagementApprovalStatus::WAITING_FOR_APPROVAL) {
            throw RegularException::forbidden('request already concluded');
        }
        $this->setTransactionValue($data->transactionValue);
        $this->note = $data->note;
    }

    //
    public function assertManageableBySales(Sales $sales): void
    {
        if (!$this->strikingAssignment->isBelongsToSales($sales)) {
            throw RegularException::forbidden('unmanaged closing request');
        }
    }
}
