<?php

namespace Resources\Domain\Data;

use Tests\TestBase;

class DataCollectionTest extends TestBase
{
    protected $dataCollection;
    protected $collection, $existingValue = 'existing value', $existingKey = 'existingKey';
    protected $value = 'value', $key = 'key';

    protected function setUp(): void
    {
        parent::setUp();
        $this->dataCollection = new TestableDataCollection();
        $this->collection = [
            $this->existingKey => $this->existingValue,
        ];
        $this->dataCollection->collection = $this->collection;
    }
    
    public function test_construct_setCollectionEmptyArray()
    {
        $dataCollection = new TestableDataCollection();
        $this->assertEquals([], $dataCollection->collection);
    }
    protected function executePush()
    {
        $this->dataCollection->push($this->value, $this->key);
    }
    public function test_push_addValueToCollectionWithKey()
    {
        $this->executePush();
        $this->collection[$this->key] = $this->value;
        $this->assertEquals($this->collection, $this->dataCollection->collection);
    }
    public function test_pushFieldData_emptyFieldId_usePhpAutoIcrementKey()
    {
        $this->key = null;
        $this->executePush();
        $this->collection[] = $this->value;
        $this->assertEquals($this->collection, $this->dataCollection->collection);
    }
    
    public function executePull()
    {
        return $this->dataCollection->pull($this->existingKey);
    }
    public function test_pullFieldData_returnFieldData()
    {
        $this->assertEquals($this->existingValue, $this->executePull());
    }
    public function test_pullFieldData_unsetPulledFieldData()
    {
        $this->executePull();
        $this->assertEquals([], $this->dataCollection->collection);
    }
    public function test_pullFieldData_fieldIdNotFound_returnNull()
    {
        $this->existingKey = 'nonExisting';
        $this->assertNull($this->executePull());
    }
    
    public function test_foreachDataCollection_iterateThroughCollection()
    {
        $this->dataCollection->collection = ['one', 'two' => 'two', 'three'];
        $collection = [];
        foreach ($this->dataCollection as $key => $value) {
            $collection[$key] = $value;
        }
        $this->assertEquals(['one', 'two' => 'two', 'three'], $collection);
    }
}

class TestableDataCollection extends DataCollection
{
    public $collection;
}
