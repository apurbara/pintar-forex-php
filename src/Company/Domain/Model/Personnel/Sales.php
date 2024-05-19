<?php

namespace Company\Domain\Model\Personnel;

use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\Personnel;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineSalesRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use SharedContext\Domain\Enum\SalesType;

#[Entity(repositoryClass: DoctrineSalesRepository::class)]
class Sales
{
    #[FetchableObject(targetEntity: Personnel::class, joinColumnName: "Personnel_id")]
    #[ManyToOne(targetEntity: Personnel::class)]
    #[JoinColumn(name: "Personnel_id", referencedColumnName: "id")]
    protected Personnel $personnel;
    
    #[FetchableObject(targetEntity: Area::class, joinColumnName: "Area_id")]
    #[ManyToOne(targetEntity: Area::class)]
    #[JoinColumn(name: "Area_id", referencedColumnName: "id")]
    protected Area $area;
    
    #[Id, Column(type: "guid")]
    protected string $id;
    
    #[Column(type: "datetimetz_immutable", nullable: true)]
    protected DateTimeImmutable $createdTime;
    
    #[Column(type: "boolean", nullable: false, options: ["default" => 0])]
    protected bool $disabled;
    
    #[Column(type: "string", enumType: SalesType::class)]
    protected SalesType $type;
    
//    #[OneToMany(targetEntity: CustomerAssignment::class, mappedBy: "sales", fetch: "EXTRA_LAZY")]
//    protected Collection $customerAssignments;


    public function __construct(Personnel $personnel, Area $area, string $id, SalesData $data)
    {
        $this->personnel = $personnel;
        $this->area = $area;
        $this->id = $id;
        $this->createdTime = new DateTimeImmutable();
        $this->disabled = false;
        $this->type = SalesType::from($data->type);
        //
        $this->personnel->assertActive();
        $this->area->assertActive();
    }
    
    public function disable(): void
    {
        $this->disabled = true;
//        
//        $criteria = Criteria::create()
//                ->andWhere(Criteria::expr()->eq('cancelled', false));
//        foreach ($this->customerAssignments->matching($criteria)->getIterator() as $customerAssignment) {
//            $customerAssignment->cancel();
//        }
    }
    
    public function enable(): void
    {
        $this->disabled = false;
    }

}
