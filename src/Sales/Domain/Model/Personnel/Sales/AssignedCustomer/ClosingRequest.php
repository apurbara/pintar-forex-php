<?php

namespace Sales\Domain\Model\Personnel\Sales\AssignedCustomer;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Exception\RegularException;
use Resources\ValidationRule;
use Resources\ValidationService;
use Sales\Domain\Model\Personnel\Sales;
use Sales\Domain\Model\Personnel\Sales\AssignedCustomer;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineClosingRequestRepository;
use SharedContext\Domain\Enum\ManagementApprovalStatus;

#[Entity(repositoryClass: DoctrineClosingRequestRepository::class)]
class ClosingRequest
{

    #[ManyToOne(targetEntity: AssignedCustomer::class, inversedBy: "closingRequest", fetch: "LAZY")]
    #[JoinColumn(name: "AssignedCustomer_id", referencedColumnName: "id")]
    protected AssignedCustomer $assignedCustomer;
    
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

    protected function setTransactionValue(int $transactionValue)
    {
        ValidationService::build()
                ->addRule(ValidationRule::notEmpty())
                ->execute($transactionValue, 'transaction value is mandatory');
        $this->transactionValue = $transactionValue;
    }

    public function __construct(AssignedCustomer $assignedCustomer, ClosingRequestData $data)
    {
        $this->assignedCustomer = $assignedCustomer;
        $this->id = $data->id;
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
        $this->assignedCustomer->assertBelongsToSales($sales);
    }

    //
    public function isOngoing(): bool
    {
        return $this->status === ManagementApprovalStatus::WAITING_FOR_APPROVAL;
    }
}
