<?php

namespace Company\Domain\Model;

use DateTimeImmutable;
use SharedContext\Domain\ValueObject\AccountInfo;
use SharedContext\Domain\ValueObject\AccountInfoData;
use Tests\TestBase;

class PersonnelTest extends TestBase
{

    protected $accountInfoData;
    protected $id = 'newPersonnelId';

    protected function setUp(): void
    {
        parent::setUp();
        $this->accountInfoData = new AccountInfoData('personnel name', 'personnel@email.org', 'password123');
    }

    //
    protected function createPersonnelData()
    {
        $personnelData = new PersonnelData($this->accountInfoData);
        $personnelData->setId($this->id);
        return $personnelData;
    }

    //
    protected function construct()
    {
        return new TestablePersonnel($this->createPersonnelData());
    }
    public function test_construct_setProperties()
    {
        $personnel = $this->construct();
        $this->assertSame($this->id, $personnel->id);
        $this->assertFalse($personnel->disabled);
        $this->assertDateTimeImmutableYmdHisValueEqualsNow($personnel->createdTime);
        $this->assertInstanceOf(AccountInfo::class, $personnel->accountInfo);
    }
}

class TestablePersonnel extends Personnel
{

    public string $id;
    public bool $disabled;
    public DateTimeImmutable $createdTime;
    public AccountInfo $accountInfo;
}
