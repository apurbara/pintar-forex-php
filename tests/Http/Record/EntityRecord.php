<?php

namespace Tests\Http\Record;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\Table;
use Illuminate\Database\ConnectionInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use Reflector;

class EntityRecord implements IRecord
{

    public string $tableName;
    public array $columns;

    public function __construct(string $classMetadata, $index)
    {
        $classReflection = new ReflectionClass($classMetadata);
        $this->tableName = $this->getAttribute($classReflection, Table::class)?->getArguments()['name'] ?? $classReflection->getShortName();
        $this->initializeColumns($classMetadata, $index);
    }

    public function insert(ConnectionInterface $connection): void
    {
        $connection->table($this->tableName)->insert($this->columns);
    }

    //
    private function initializeColumns(string $classMetadata, $index, string $columnPrefix = null)
    {
        $classReflection = new ReflectionClass($classMetadata);
        foreach ($this->iterateReflectionPropertiesOfClass($classMetadata) as $propertyReflection) {
            $columnAttributeReflection = $this->getAttribute($propertyReflection, Column::class);
            if ($columnAttributeReflection) {
                $colName = $columnPrefix . $propertyReflection->getName();
                $this->columns[$colName] = $this->getInitialColumnValue($columnAttributeReflection,
                        $propertyReflection->getName(), $index);
                continue;
            }

            $joinColumnAttributeReflection = $this->getAttribute($propertyReflection, JoinColumn::class);
            if ($joinColumnAttributeReflection) {
                $this->columns[$joinColumnAttributeReflection->getArguments()['name']] = null;
                continue;
            }

            $embeddedAttributeReflection = $this->getAttribute($propertyReflection, Embedded::class);
            if ($embeddedAttributeReflection) {
                $this->initializeColumns(
                    $embeddedAttributeReflection->getArguments()['class'], 
                    $index, 
                    $embeddedAttributeReflection->getArguments()['columnPrefix'] ?? null
                );
                continue;
            }
        }
    }

    /**
     * 
     * @param string $classMetadata
     * @return ReflectionProperty[]
     */
    protected function iterateReflectionPropertiesOfClass(string $classMetadata)
    {
        $classReflection = new ReflectionClass($classMetadata);
        return $classReflection->getProperties();
    }

    protected function getInitialColumnValue(ReflectionAttribute $columnAttributeReflection, string $colName, $index)
    {
        return match ($columnAttributeReflection->getArguments()['type']) {
            'guid' => strtolower($this->tableName) . "-{$index}-id",
            'boolean' => false,
            'integer', 'smallint', 'bigint' => 999,
            'float', 'decimal' => 999.99,
            'string', 'text', 'binary', 'blob' => "{$this->tableName} $index $colName",
            'json' => json_encode([]),
            'datetimetz_immutable', 'datetimetz', 'datetime', 'datetime_immutable' => (new DateTimeImmutable('-1 months'))->format('Y-m-d H:i:s'),
        };
    }

    protected function getAttribute(Reflector $reflection, string $attributeMetadata): ?\ReflectionAttribute
    {
        $attributesReflection = $reflection->getAttributes($attributeMetadata, \ReflectionAttribute::IS_INSTANCEOF);
        return $attributesReflection[0] ?? null;
    }
}
