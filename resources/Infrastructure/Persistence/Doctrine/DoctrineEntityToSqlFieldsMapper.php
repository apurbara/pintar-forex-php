<?php

namespace Resources\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use ReflectionClass;
use Resources\Attributes\Composed;
use Resources\Infrastructure\GraphQL\Attributes\ExcludeFromFetch;
use Resources\ReflectionHelper;

class DoctrineEntityToSqlFieldsMapper
{

    public static function mapFields(string $classMetadata, array $excludedProperties = [], ?string $columnPrefix = null, ?string $tableName = null): array
    {
        $excludedProperties = [
            ...$excludedProperties,
            "password",
        ];

        $fields = [
            'selectFields' => [],
            'joins' => [
//                [
//                    "from" => "",
//                    "join" => "",
//                    "condition" => "",
//                ],
            ],
        ];

        $classReflection = new ReflectionClass($classMetadata);
        $tableName = $tableName ?? ReflectionHelper::getAttributeArgument($classReflection, Table::class, "name") ?? $classReflection->getShortName();
        foreach ($classReflection->getProperties() as $propertyReflection) {
            if (in_array($propertyReflection->getName(), $excludedProperties) || ReflectionHelper::hasAttribute($propertyReflection,
                            ExcludeFromFetch::class)
            ) {
                continue;
            }

            $columnAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, Column::class);
            if ($columnAttributeReflection) {
                $colName = $columnPrefix . $propertyReflection->getName();
//                $fields['selectFields'][] = $columnPrefix . $propertyReflection->getName();
                $colName = $columnPrefix . $propertyReflection->getName();
                $fields['selectFields'][] = "{$tableName}.{$colName} {$colName}";
                continue;
            }

            $embeddedAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, Embedded::class);
            if ($embeddedAttributeReflection) {
                $fields['selectFields'] = [
                    ...$fields['selectFields'],
                    ...static::mapFields(
                            $embeddedAttributeReflection->getArguments()['class'], [],
                            $embeddedAttributeReflection->getArguments()['columnPrefix'] ?? null, $tableName,
                    )['selectFields']
                ];
                continue;
            }

            $composedAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, Composed::class);
            if ($composedAttributeReflection) {
                $oneToOneAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, OneToOne::class);
                
                $joinColumnAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, JoinColumn::class);
                if ($oneToOneAttributeReflection && $joinColumnAttributeReflection) {
                    $composedReflection = new ReflectionClass($oneToOneAttributeReflection->getArguments()['targetEntity']);
                    $composedTableName = ReflectionHelper::getAttributeArgument($composedReflection, Table::class, "name") ?? $composedReflection->getShortName();
                    $joinColName = $joinColumnAttributeReflection->getArguments()['name'];
                    $referenceColName = $joinColumnAttributeReflection->getArguments()['referencedColumnName'] ?? "id";
                    $fields['joins'][] = [
                        "from" => $tableName,
                        "to" => $composedTableName,
                        "condition" => "{$tableName}.{$joinColName} = {$composedTableName}.{$referenceColName}",
                    ];
                }
                
                $composedFields = static::mapFields($oneToOneAttributeReflection->getArguments()['targetEntity'], ["id"]);
                $fields = [
                    "selectFields" => [
                        ...$fields["selectFields"],
                        ...$composedFields["selectFields"],
                    ], 
                    "joins" => [
                        ...$fields["joins"],
                        ...$composedFields["joins"],
                    ], 
                ];
                continue;
            }
            
            $joinColumnAttributeReflection = ReflectionHelper::findAttribute($propertyReflection, JoinColumn::class);
            if ($joinColumnAttributeReflection) {
                $fields["selectFields"][] = $joinColumnAttributeReflection->getArguments()['name'];
            }
        }
        return $fields;
    }
}
