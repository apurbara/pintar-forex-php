<?php

namespace SharedContext\Domain\ValueObject;

use Tests\TestBase;

class LabelTest extends TestBase
{

    protected $data;
    protected $label;
    protected $otherLabel;
    protected $title = 'new label title', $description = 'new label description';

    protected function setUp(): void
    {
        parent::setUp();
        $data = new LabelData('title', 'description');
        $this->label = new TestableLabel($data);

        $otherData = new LabelData('other title', 'other description');
        $this->otherLabel = new TestableLabel($otherData);
    }

    protected function buildLabelData()
    {
        return new LabelData($this->title, $this->description);
    }
    
    //
    protected function executeConstruct()
    {
        return new TestableLabel($this->buildLabelData());
    }

    public function test_construct_setProperties()
    {
        $label = $this->executeConstruct();
        $this->assertEquals($this->title, $label->title);
        $this->assertEquals($this->description, $label->description);
    }

    public function test_construct_emptyTitle_badRequest()
    {
        $this->title = '';
        $operation = function () {
            $this->executeConstruct();
        };
        $this->assertRegularExceptionThrowed($operation, 'Bad Request', 'bad request: title is mandatory');
    }

    //
    protected function update()
    {
        return $this->label->update($this->buildLabelData());
    }

    public function test_update_returnUpdatedLabel()
    {
        $newLabel = $this->update();
        $this->assertSame($this->title, $newLabel->title);
        $this->assertSame($this->description, $newLabel->description);
    }

    //
    protected function sameValueAs()
    {
        return $this->label->sameValueAs($this->otherLabel);
    }

    public function test_sameValueAs_differentValue_returnFalse()
    {
        $this->assertFalse($this->sameValueAs());
    }

    public function test_sameValueAs_sameValue_returnTrue()
    {
        $this->otherLabel->title = $this->label->title;
        $this->otherLabel->description = $this->label->description;
        $this->assertTrue($this->sameValueAs());
    }
}

class TestableLabel extends Label
{

    public string $title;
    public ?string $description = null;
}
