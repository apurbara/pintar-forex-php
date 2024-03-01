<?php

namespace Resources\Infrastructure\GraphQL;

use Doctrine\DBAL\Schema\Table;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\JoinColumn;
use GraphQL\Type\Definition\Type;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionEnum;
use ReflectionProperty;
use Resources\Attributes\Composed;
use Resources\Infrastructure\GraphQL\Attributes\ExcludeFromFetch;
use Resources\Infrastructure\GraphQL\Attributes\ExcludeFromInput;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObject;
use Resources\Infrastructure\GraphQL\Attributes\FetchableObjectList;
use Resources\Infrastructure\GraphQL\Attributes\IncludeAsInput;
use Resources\Infrastructure\GraphQL\Attributes\IncludeAsInputList;
use Resources\Infrastructure\GraphQL\CustomTypes\DateTimeZ;
use Resources\Infrastructure\Persistence\Doctrine\Repository\SearchCategory\Filter;
use Resources\ReflectionHelper;
use function app;

class DoctrineEntityToGraphqlFieldMapper
{

    private static function mapDoctrineColumn(array &$fields, ReflectionProperty $propertyReflection,
            ?string $columnPrefix = null): bool
    {
        $columnAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, Column::class);
        if ($columnAttributeReflection) {
            $colName = $columnPrefix . $propertyReflection->getName();
            $fields[$colName] = static::getAssociateGraphqlTypeFromDoctrineColumn($columnAttributeReflection);
            return true;
        }
        return false;
    }

    private static function mapEmbeddedObjectFields(array &$fields, ReflectionProperty $propertyReflection): bool
    {
        $embeddedAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, Embedded::class);
        if ($embeddedAttributeReflection) {
            $fields = [
                ...$fields,
                ...static::mapObjectFields(
                        $embeddedAttributeReflection->getArguments()['class'], [],
                        $embeddedAttributeReflection->getArguments()['columnPrefix'] ?? null,
                ),
            ];
            return true;
        }
        return false;
    }

    private static function mapCompositionObjectFields(array &$fields, ReflectionProperty $propertyReflection): bool
    {
        $composedAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, Composed::class);
        if ($composedAttributeReflection) {
            $fields = [
                ...$fields,
                ...static::mapObjectFields($composedAttributeReflection->getArguments()['class'], ['id']),
            ];
            return true;
        }
        return false;
    }

    private static function mapDoctrineJoinColumn(array &$fields, ReflectionProperty $propertyReflection): void
    {
        $joinColumnAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, JoinColumn::class);
        if ($joinColumnAttributeReflection) {
            $joinColumnName = $joinColumnAttributeReflection->getArguments()['name'];
            $fields[$joinColumnName] = Type::id();
        }
    }

    private static function mapFetchableObject(array &$fields, ReflectionProperty $propertyReflection): bool
    {
        $fetchableObjectAttributeReflection = ReflectionHelper::findAttribute($propertyReflection,
                        FetchableObject::class);
        if ($fetchableObjectAttributeReflection) {
            $targetEntityMetadata = $fetchableObjectAttributeReflection->getArguments()['targetEntity'];
            $joinColumnName = $fetchableObjectAttributeReflection->getArguments()['joinColumnName'];
            $referenceColumnName = $fetchableObjectAttributeReflection->getArguments()['referenceColumnName'] ?? null;
            $fields[$propertyReflection->getName()] = [
                'type' => TypeRegistry::objectType($targetEntityMetadata),
                'resolve' => function($root) use($joinColumnName, $referenceColumnName, $targetEntityMetadata) {
                    if (empty($referenceColumnName)) {
                        return empty($root[$joinColumnName]) ? null : app(EntityManager::class)->getRepository($targetEntityMetadata)
                                ->queryOneById($root[$joinColumnName]);
                    } else {
                        return app(EntityManager::class)->getRepository($targetEntityMetadata)->queryOneBy([
                                    new Filter($root[$joinColumnName], $referenceColumnName)
                                ]);
                    }
                
                }
//                'resolve' => fn($root) => empty($root[$joinColumnName]) ? null : app(EntityManager::class)->getRepository($targetEntityMetadata)
//                        ->queryOneById($root[$joinColumnName])
            ];
            return true;
        }
        return false;
    }

    private static function mapFetchableObjectList(array &$fields, ReflectionProperty $propertyReflection): bool
    {
        $fetchableObjectListAttributeReflection = ReflectionHelper::findAttribute($propertyReflection,
                        FetchableObjectList::class);
        if ($fetchableObjectListAttributeReflection) {
            $joinColumnName = $fetchableObjectListAttributeReflection->getArguments()['joinColumnName'];
            $targetEntityMetadata = $fetchableObjectListAttributeReflection->getArguments()['targetEntity'];
            $targetEntityReflectionClass = new \ReflectionClass($targetEntityMetadata);
            $targetColumnName = ReflectionHelper::findAttribute($targetEntityReflectionClass, Table::class)?->getArguments()['name'] ??
                    $targetEntityReflectionClass->getShortName();
            if (isset($fetchableObjectListAttributeReflection->getArguments()['paginationRequired'])) {
                $fields[$propertyReflection->getName()] = [
                    'type' => TypeRegistry::paginationType($targetEntityMetadata),
//                    'type' => new Pagination(TypeRegistry::objectType($targetEntityMetadata)),
                    'args' => InputListSchema::paginationListSchema(),
                    'resolve' => function ($root, $args) use ($targetEntityMetadata, $targetColumnName, $joinColumnName) {
                        $paginationSchema = $args;
                        $paginationSchema['filters'][] = [
                            'column' => "{$targetColumnName}.{$joinColumnName}", 'value' => $root['id']
                        ];
                        return app(EntityManager::class)->getRepository($targetEntityMetadata)
                                ->queryPaginationList($paginationSchema);
                    }
                ];
            } else {
                $fields[$propertyReflection->getName()] = [
                    'type' => Type::listOf(TypeRegistry::objectType($targetEntityMetadata)),
                    'args' => InputListSchema::allListSchema(),
                    'resolve' => function ($root, $args) use ($targetEntityMetadata, $targetColumnName, $joinColumnName) {
                        $searchSchema = $args;
                        $searchSchema['filters'][] = [
                            'column' => "{$targetColumnName}.{$joinColumnName}", 'value' => $root['id']
                        ];
                        return app(EntityManager::class)->getRepository($targetEntityMetadata)
                                ->queryAllList($searchSchema);
                    }
                ];
            }
            return true;
        }
        return false;
    }

    public static function mapObjectFields(string $classMetadata, array $excludedProperties = [],
            ?string $columnPrefix = null): array
    {
        $excludedProperties = [
            ...$excludedProperties,
            "password",
        ];

        $fields = [];
        $classReflection = new ReflectionClass($classMetadata);
        foreach ($classReflection->getProperties() as $propertyReflection) {
            if (in_array($propertyReflection->getName(), $excludedProperties) || ReflectionHelper::hasAttribute($propertyReflection,
                            ExcludeFromFetch::class)
            ) {
                continue;
            }

            if (static::mapDoctrineColumn($fields, $propertyReflection)) {
                continue;
            }
            if (static::mapEmbeddedObjectFields($fields, $propertyReflection)) {
                continue;
            }
            if (static::mapCompositionObjectFields($fields, $propertyReflection)) {
                continue;
            }
            static::mapDoctrineJoinColumn($fields, $propertyReflection);
            if (static::mapFetchableObject($fields, $propertyReflection)) {
                continue;
            }
            if (static::mapFetchableObjectList($fields, $propertyReflection)) {
                continue;
            }

//            $columnAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, Column::class);
//            if ($columnAttributeReflection) {
//                $colName = $columnPrefix . $propertyReflection->getName();
//                $fields[$colName] = static::getAssociateGraphqlTypeFromDoctrineColumn($columnAttributeReflection);
//                continue;
//            }
//            $embeddedAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, Embedded::class);
//            if ($embeddedAttributeReflection) {
//                $fields = [
//                    ...$fields,
//                    ...static::mapObjectFields(
//                            $embeddedAttributeReflection->getArguments()['class'], [],
//                            $embeddedAttributeReflection->getArguments()['columnPrefix'] ?? null,
//                    ),
//                ];
//                continue;
//            }
//            $composedAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, Composed::class);
//            if ($composedAttributeReflection) {
//                $fields = [
//                    ...$fields,
//                    ...static::mapObjectFields($composedAttributeReflection->getArguments()['class'], ['id']),
//                ];
//                continue;
//            }
//            $joinColumnAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, JoinColumn::class);
//            if ($joinColumnAttributeReflection) {
//                $joinColumnName = $joinColumnAttributeReflection->getArguments()['name'];
//                $fields[$joinColumnName] = Type::id();
//
//                $fetchableObjectAttributeReflection = ReflectionHelper::findAttribute($propertyReflection,
//                                FetchableObject::class);
//                if ($fetchableObjectAttributeReflection) {
//                    $targetEntityMetadata = $fetchableObjectAttributeReflection->getArguments()['targetEntity'];
//                    $fields[$propertyReflection->getName()] = [
//                        'type' => TypeRegistry::objectType($targetEntityMetadata),
//                        'resolve' => fn($root) => app(EntityManager::class)->getRepository($targetEntityMetadata)
//                                ->fetchOneById($root[$joinColumnName])
//                    ];
//                }
//                continue;
//            }
//            $fetchableObjectAttributeReflection = ReflectionHelper::findAttribute($propertyReflection,
//                            FetchableObject::class);
//            if ($fetchableObjectAttributeReflection) {
//                $targetEntityMetadata = $fetchableObjectAttributeReflection->getArguments()['targetEntity'];
//                $joinColumnName = $fetchableObjectAttributeReflection->getArguments()['joinColumnName'];
//                $fields[$propertyReflection->getName()] = [
//                    'type' => TypeRegistry::objectType($targetEntityMetadata),
//                    'resolve' => fn($root) => app(EntityManager::class)->getRepository($targetEntityMetadata)
//                            ->fetchOneById($root[$joinColumnName])
//                ];
//                continue;
//            }
//            $fetchableObjectListAttributeReflection = ReflectionHelper::findAttribute($propertyReflection,
//                            FetchableObjectList::class);
//            if ($fetchableObjectListAttributeReflection) {
//                $mappedColumnName = $fetchableObjectListAttributeReflection->getArguments()['joinColumnName'];
//                $targetEntityMetadata = $fetchableObjectAttributeReflection->getArguments()['targetEntity'];
//                $targetEntityReflectionClass = new \ReflectionClass($targetEntityMetadata);
//                $targetColumnName = ReflectionHelper::findAttribute($targetEntityReflectionClass, Table::class)?->getArguments()['name'] ?? $targetEntityReflectionClass->getShortName();
//                if ($fetchableObjectListAttributeReflection->getArguments()['paginationRequired']) {
//                    $fields[$propertyReflection->getName()] = [
//                        'type' => new Pagination(TypeRegistry::objectType($targetEntityMetadata)),
//                        'args' => InputListSchema::paginationListSchema(),
//                        'resolve' => function ($root, $args) use ($targeEntityMetadata, $targetColumnName,
//                                $mappedColumnName) {
//                            $paginationSchema = $args;
//                            $paginationSchema['filters'][] = [
//                                'colum' => "{$targetColumnName}.{$mappedColumnName}", 'value' => $root['id']
//                            ];
//                            return app(EntityManager::class)->getRepository($targetEntityMetadata)
//                                    ->queryPaginationList($paginationSchema);
//                        }
//                    ];
//                } else {
//                    $fields[$propertyReflection->getName()] = [
//                        'type' => new Pagination(TypeRegistry::objectType($targetEntityMetadata)),
//                        'args' => InputListSchema::allListSchema(),
//                        'resolve' => function ($root, $args) use ($targeEntityMetadata, $targetColumnName,
//                                $mappedColumnName) {
//                            $searchSchema = $args;
//                            $searchSchema['filters'][] = [
//                                'colum' => "{$targetColumnName}.{$mappedColumnName}", 'value' => $root['id']
//                            ];
//                            return app(EntityManager::class)->getRepository($targetEntityMetadata)
//                                    ->queryAllList($searchSchema);
//                        }
//                    ];
//                }
//                continue;
//            }
        }

        return $fields;
    }

    //
    private static function mapEmbeddedInputFields(array &$fields, ReflectionProperty $propertyReflection): bool
    {
        $embeddedAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, Embedded::class);
        if ($embeddedAttributeReflection) {
            $fields = [
                ...$fields,
                ...static::mapInputFields(
                        $embeddedAttributeReflection->getArguments()['class'], [],
                        $embeddedAttributeReflection->getArguments()['columnPrefix'] ?? null,
                ),
            ];
            return true;
        }
        return false;
    }

    private static function mapCompositionInputFields(array &$fields, ReflectionProperty $propertyReflection): bool
    {
        $composedAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, Composed::class);
        if ($composedAttributeReflection) {
            $fields = [
                ...$fields,
                ...static::mapInputFields($composedAttributeReflection->getArguments()['class'], ['id']),
            ];
            return true;
        }
        return false;
    }

    private static function mapInputAsInputList(array &$fields, ReflectionProperty $propertyReflection): bool
    {
        $includeAsInputListAttributeReflection = ReflectionHelper::findAttribute($propertyReflection,
                        IncludeAsInputList::class);
        if ($includeAsInputListAttributeReflection) {
            $fields[$propertyReflection->getName()] = Type::listOf(TypeRegistry::inputType($includeAsInputListAttributeReflection->getArguments()['targetEntity']));
            return true;
        }
        return false;
    }

    private static function mapIncludeAsInput(array &$fields, ReflectionProperty $propertyReflection): bool
    {
        $includeAsInputAttributeReflection = ReflectionHelper::findAttribute($propertyReflection,
                        IncludeAsInput::class);
        if ($includeAsInputAttributeReflection) {
            $fields[$propertyReflection->getName()] = TypeRegistry::inputType($includeAsInputAttributeReflection->getArguments()['targetEntity']);
            return true;
        }
        return false;
    }

    public static function mapInputFields(string $classMetadata, array $excludedProperties = [],
            ?string $columnPrefix = null): array
    {
        $excludedProperties = [
            ...$excludedProperties,
            "createdTime", "lastModifiedTime", "submitTime", "registrationTime", "concludedTime",
            "disabled", "cancelled", "suspended", "removed"
        ];

        $fields = [];
        $classReflection = new ReflectionClass($classMetadata);
        foreach ($classReflection->getProperties() as $propertyReflection) {
            if (in_array($propertyReflection->getName(), $excludedProperties) || ReflectionHelper::hasAttribute($propertyReflection,
                            ExcludeFromInput::class)
            ) {
                continue;
            }

            if (static::mapDoctrineColumn($fields, $propertyReflection)) {
                continue;
            }
            if (static::mapEmbeddedInputFields($fields, $propertyReflection)) {
                continue;
            }
            if (static::mapCompositionInputFields($fields, $propertyReflection)) {
                continue;
            }
            static::mapDoctrineJoinColumn($fields, $propertyReflection);
            if (static::mapInputAsInputList($fields, $propertyReflection)) {
                continue;
            }
            if (static::mapIncludeAsInput($fields, $propertyReflection)) {
                continue;
            }

//            $columnAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, Column::class);
//            if ($columnAttributeReflection) {
//                $colName = $columnPrefix . $propertyReflection->getName();
//                $fields[$colName] = static::getAssociateGraphqlTypeFromDoctrineColumn($columnAttributeReflection);
//                continue;
//            }
//            $embeddedAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, Embedded::class);
//            if ($embeddedAttributeReflection) {
//                $fields = [
//                    ...$fields,
//                    ...static::mapInputFields(
//                            $embeddedAttributeReflection->getArguments()['class'], [],
//                            $embeddedAttributeReflection->getArguments()['columnPrefix'] ?? null,
//                    ),
//                ];
//                continue;
//            }
//            $composedAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, Composed::class);
//            if ($composedAttributeReflection) {
//                $fields = [
//                    ...$fields,
//                    ...static::mapInputFields($composedAttributeReflection->getArguments()['class'], ['id']),
//                ];
//                continue;
//            }
//            $joinColumnAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, JoinColumn::class);
//            if ($joinColumnAttributeReflection) {
//                $fields[$joinColumnAttributeReflection->getArguments()['name']] = Type::id();
//                continue;
//            }

//            $includeAsInputListAttributeReflection = ReflectionHelper::findAttribute($propertyReflection,
//                            IncludeAsInputList::class);
//            if ($includeAsInputListAttributeReflection) {
//                $fields[$propertyReflection->getName()] = Type::listOf(TypeRegistry::inputType($includeAsInputListAttributeReflection->getArguments()['targetEntity']));
//                continue;
//            }
        }
        return $fields;
    }

    public static function mapEnumValues(string $classMetadata): array
    {
        $values = [];
        $enumReflection = new ReflectionEnum($classMetadata);
        foreach ($enumReflection->getCases() as $caseReflection) {
            $values[$caseReflection->getName()] = $caseReflection->getBackingValue();
        }
        return $values;
    }

    //
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
}
