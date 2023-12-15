<?php

namespace Resources\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\JoinColumn;
use GraphQL\Type\Definition\Type;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionEnum;
use ReflectionProperty;
use Reflector;
use Resources\Attributes\Composed;
use Resources\Attributes\ExcludeFromFetch;
use Resources\Attributes\ExcludeFromInput;
use Resources\Attributes\FetchableEntity;
use Resources\Attributes\IncludeInInput;
use Resources\Infrastructure\GraphQL\CustomTypes\DateTimeZ;
use Resources\Infrastructure\GraphQL\TypeRegistry;
use function app;

class DoctrineGraphqlFieldsBuilder
{

    /**
     * 
     * @param string $classMetadata
     * @param string|null $columnPrefix
     * @param bool $excludeId set true for compositionObject
     * @return array
     */
    public static function buildObjectFields(
            string $classMetadata, ?string $columnPrefix = null, bool $excludeId = false): array
    {
        $fields = [];
        foreach (static::iterateReflectionPropertiesOfClass($classMetadata) as $propertyReflection) {
            if ($excludeId && $propertyReflection->getName() === 'id') {
                continue;
            }

            // always exclude password from fetching
            if ($propertyReflection->getName() === 'password') {
                continue;
            }

            $excludeFromFetchAttributeReflection = static::getAttribute($propertyReflection, ExcludeFromFetch::class);
            if ($excludeFromFetchAttributeReflection) {
                continue;
            }

            $columnAttributeReflection = static::getAttribute($propertyReflection, Column::class);
            if ($columnAttributeReflection) {
                $colName = $columnPrefix . $propertyReflection->getName();
                $fields[$colName] = static::getAssociateGraphqlTypeFromDoctrineColumn($columnAttributeReflection);
                continue;
            }

            $joinAttributeReflection = static::getAttribute($propertyReflection, JoinColumn::class);
            if ($joinAttributeReflection) {
                $colName = $columnPrefix . $joinAttributeReflection->getArguments()['name'];
                $fields[$colName] = Type::id();
//                continue;
            }

            $embeddedAttributeReflection = static::getAttribute($propertyReflection, Embedded::class);
            if ($embeddedAttributeReflection) {
                $fields = [
                    ...$fields,
                    ...static::buildObjectFields(
                            $embeddedAttributeReflection->getArguments()['class'],
                            $embeddedAttributeReflection->getArguments()['columnPrefix'] ?? null,
                    ),
                ];
                continue;
            }

            $composedAttributeReflection = static::getAttribute($propertyReflection, Composed::class);
            if ($composedAttributeReflection) {
                $fields = [
                    ...$fields,
                    ...static::buildObjectFields($composedAttributeReflection->getArguments()['class'], null, true),
                ];
                continue;
            }

            $fetchableEntityAttributeReflection = static::getAttribute($propertyReflection, FetchableEntity::class);
            if ($fetchableEntityAttributeReflection) {
                $targetEntityMetadata = $fetchableEntityAttributeReflection->getArguments()['targetEntity'];
                $joinColumnName = $fetchableEntityAttributeReflection->getArguments()['joinColumnName'];

                $repositoryClass = app(EntityManager::class)->getRepository($targetEntityMetadata);
                $fields[$propertyReflection->getName()] = [
                    'type' => TypeRegistry::objectType($targetEntityMetadata),
                    'resolve' => function ($root) use ($repositoryClass, $joinColumnName) {
                        if ($root[$joinColumnName]) {
                            return $repositoryClass->fetchOneById($root[$joinColumnName]);
                        } else {
                            return null;
                        }
                    }
//                    'resolve' => fn($root) => $repositoryClass->fetchOneById($root[$joinColumnName])
                ];
                continue;
            }
        }
        return $fields;
    }

    public static function buildInputFields(string $classMetadata, ?string $columnPrefix = null): array
    {
        $fields = [];
        foreach (static::iterateReflectionPropertiesOfClass($classMetadata) as $propertyReflection) {
            $excludeFromInputAttributeReflection = static::getAttribute($propertyReflection, ExcludeFromInput::class);
            if ($excludeFromInputAttributeReflection) {
                continue;
            }

            // always exclude id, lifecycle status, time signature from input
            $isFieldUncontrollerByUser = match ($propertyReflection->getName()) {
                'id' => true,
                'disabled', 'cancelled', 'removed' => true,
                'createdTime', 'lastModifiedTime', 'submitTime' => true,
                default => false,
            };
            if ($isFieldUncontrollerByUser && !static::getAttribute($propertyReflection, IncludeInInput::class)) {
                continue;
            }

            $columnAttributeReflection = static::getAttribute($propertyReflection, Column::class);
            if ($columnAttributeReflection) {
                $colName = $columnPrefix . $propertyReflection->getName();
                $fields[$colName] = static::getAssociateGraphqlTypeFromDoctrineColumn($columnAttributeReflection);
                continue;
            }

            $embeddedAttributeReflection = static::getAttribute($propertyReflection, Embedded::class);
            if ($embeddedAttributeReflection) {
                $fields = [
                    ...$fields,
                    ...static::buildInputFields(
                            $embeddedAttributeReflection->getArguments()['class'],
                            $embeddedAttributeReflection->getArguments()['columnPrefix'] ?? null,
                    ),
                ];
                continue;
            }

            $composedAttributeReflection = static::getAttribute($propertyReflection, Composed::class);
            if ($composedAttributeReflection) {
                $fields = [
                    ...$fields,
                    ...static::buildInputFields($composedAttributeReflection->getArguments()['class']),
                ];
                continue;
            }
        }
        return $fields;
    }

    public static function getEnumValues(string $classMetadata): array
    {
        $values = [];
        $enumReflection = new ReflectionEnum($classMetadata);
        foreach ($enumReflection->getCases() as $caseReflection) {
            $values[$caseReflection->getName()] = $caseReflection->getBackingValue();
        }
        return $values;
    }

    /**
     * 
     * @param string $classMetadata
     * @return ReflectionProperty[]
     */
    private static function iterateReflectionPropertiesOfClass(string $classMetadata)
    {
        $classReflection = new ReflectionClass($classMetadata);
        return $classReflection->getProperties();
    }

    private static function getAssociateGraphqlTypeFromDoctrineColumn(ReflectionAttribute $columnAttributeReflection)
    {
//        $enumType = $columnAttributeReflection->getArguments()['enumType'];
//        if (isset($enumType)) {
//            return TypeRegistry::enumType($enumType);
//        }
        return match ($columnAttributeReflection->getArguments()['type']) {
            'guid' => Type::id(),
            'boolean' => Type::boolean(),
            'integer', 'smallint', 'bigint' => Type::int(),
            'float', 'decimal' => Type::float(),
            'string', 'text', 'binary', 'blob' => Type::string(),
            'json' => Type::string(),
            'datetimetz_immutable', 'datetimetz', 'datetime', 'datetime_immutable' => TypeRegistry::type(DateTimeZ::class),
        };
    }

    private static function getAttribute(Reflector $reflection, string $attributeMetadata): ?\ReflectionAttribute
    {
        $attributesReflection = $reflection->getAttributes($attributeMetadata, \ReflectionAttribute::IS_INSTANCEOF);
        return $attributesReflection[0] ?? null;
    }
}
