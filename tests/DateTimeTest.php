<?php

namespace Tests;

class DateTimeTest extends TestBase
{
    protected $isoTime = "2023-12-12T14:00:00+07:00";
    
    protected function setUp(): void
    {
        parent::setUp();
    }
    
    public function test_converFromIso()
    {
        $dateTime = new \DateTime('now', new \DateTimeZone('Asia/Jakarta'));
//        $dateTime = new \DateTimeImmutable($this->isoTime);
        var_dump($dateTime->getTimezone());
        var_dump($dateTime->format('Y-m-d H:i:sP'));
    }
}
