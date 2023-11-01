<?php

namespace Sales\Domain\Model\AreaStructure\Area;

use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Resources\Uuid;
use Resources\ValidationRule;
use Resources\ValidationService;
use Sales\Domain\Model\AreaStructure\Area;
use Sales\Domain\Model\AreaStructure\Area\Customer\VerificationReport;
use Sales\Domain\Model\AreaStructure\Area\Customer\VerificationReportData;
use Sales\Domain\Model\CustomerVerification;
use Sales\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerRepository;

#[Entity(repositoryClass: DoctrineCustomerRepository::class)]
class Customer
{

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
    
    #[OneToMany(targetEntity: VerificationReport::class, mappedBy: "customer", cascade: ["persist"])]
    protected Collection $verificationReports;

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

    //
    public function submitVerificationReport(
            CustomerVerification $customerVerification, VerificationReportData $verificationReportData): void
    {
        $p = fn(VerificationReport $verificationReport) => $verificationReport->associateWithCustomerVerification($customerVerification);
        $verificationReport = $this->verificationReports->filter($p)->first();
        if ($verificationReport) {
            $verificationReport->update($verificationReportData);
        } else {
            $verificationReportData->setId(Uuid::generateUuid4());
            $verificationReport = new VerificationReport($this, $customerVerification, $verificationReportData);
            $this->verificationReports->add($verificationReport);
        }
        
    }
}
