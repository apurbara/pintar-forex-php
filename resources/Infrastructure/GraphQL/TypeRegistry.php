<?php

namespace Resources\Infrastructure\GraphQL;

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use ReflectionClass;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\CustomTypes\DateTimeZ;
use Resources\Infrastructure\GraphQL\ViewList\FilterInput;
use Resources\Infrastructure\GraphQL\ViewList\KeywordSearchInput;
use Resources\Infrastructure\GraphQL\ViewPaginationList\CursorLimitInput;
use Resources\Infrastructure\GraphQL\ViewPaginationList\OffsetLimitInput;
use Resources\Infrastructure\Persistence\Doctrine\DoctrineGraphqlFieldsBuilder;

class TypeRegistry
{

    const PREDEFINED_CLASS_MAP = [
        'KeywordSearchInput' => KeywordSearchInput::class,
        'FilterInput' => FilterInput::class,
        'CursorLimitInput' => CursorLimitInput::class,
        'OffsetLimitInput' => OffsetLimitInput::class,
        'DateTimeZ' => DateTimeZ::class,
    ];

    private static $types = [];

    public function __construct()
    {
        $this->types = [];
    }

    protected static function getPredefinedClassMap(): array
    {
        return static::PREDEFINED_CLASS_MAP;
    }

    //    private static $called = 0;

    public static function get(string $classname): Type
    {
        //static::$called += 1;
        //if (static::$called > 100) {
        //    throw RegularException::tooManyRequests('too many request');
        //}

        $path = explode('\\', $classname);
        $cacheName = lcfirst(array_pop($path));

        //        $instance = static::$types[$cacheName] ?? null;
        //        $diffInstance = class_exists($classname) && !($instance instanceof  $classname);
        //        if (!isset(static::$types[$cacheName]) || $diffInstance ) {

        if (!isset(static::$types[$cacheName])) {
            $predefinedClassMap = array_merge(self::PREDEFINED_CLASS_MAP, static::getPredefinedClassMap());
            if (class_exists($classname)) {
                static::$types[$cacheName] = new $classname();
            } elseif (isset($predefinedClassMap[$classname]) && class_exists($predefinedClassMap[$classname])) {
                $class = $predefinedClassMap[$classname];
                static::$types[$cacheName] = new $class();
            } else {
                throw RegularException::notFound("not found: $cacheName graphql type not found");
            }
        }
        return static::$types[$cacheName];
    }

    public static function getPagination(string $classname): Type
    {
        $path = explode('\\', $classname);
        $cacheName = lcfirst(array_pop($path));
        $listCacheName = $cacheName . 'List';

        if (!isset(static::$types[$cacheName])) {
            if (class_exists($classname)) {
                static::$types[$cacheName] = new $classname();
            } else {
                throw RegularException::notFound("not found: $cacheName graphql type not found");
            }
        }

        if (!isset(static::$types[$listCacheName])) {
            static::$types[$listCacheName] = new Pagination(static::$types[$cacheName], $listCacheName);
        }
        return static::$types[$listCacheName];
    }

    public static function objectType(string $classMetadata): Type
    {
        $reflectionClass = new ReflectionClass($classMetadata);
        if ($reflectionClass->isSubclassOf(ObjectType::class)) {
            $cacheName = $reflectionClass->getShortName();
            if (!isset(static::$types[$cacheName])) {
                static::$types[$cacheName] = $reflectionClass->newInstance();
            }
        } else {
            $cacheName = $reflectionClass->getShortName() . 'Graph';
            if (!isset(static::$types[$cacheName])) {
                static::$types[$cacheName] = new ObjectType([
                    'name' => $cacheName,
                    'fields' => fn () => DoctrineGraphqlFieldsBuilder::buildObjectFields($classMetadata),
                ]);
            }
        }
        return static::$types[$cacheName];
    }

    public static function inputType(string $classMetadata): Type
    {
        $reflectionClass = new ReflectionClass($classMetadata);
        if ($reflectionClass->isSubclassOf(InputObjectType::class)) {
            $cacheName = $reflectionClass->getShortName();
            if (!isset(static::$types[$cacheName])) {
                static::$types[$cacheName] = $reflectionClass->newInstance();
            }
        } else {
            $cacheName = $reflectionClass->getShortName() . 'Input';
            if (!isset(static::$types[$cacheName])) {
                static::$types[$cacheName] = new InputObjectType([
                    'name' => $cacheName,
                    'fields' => fn () => DoctrineGraphqlFieldsBuilder::buildInputFields($classMetadata),
                ]);
            }
        }
        return static::$types[$cacheName];
    }

    public static function customType(string $classMetadata): Type
    {
        $reflectionClass = new ReflectionClass($classMetadata);
        if (!$reflectionClass->isSubclassOf(Type::class)) {
            throw RegularException::badRequest('can only register valid graphql type');
        }
        $cacheName = $reflectionClass->getShortName();
        if (!isset(static::$types[$cacheName])) {
            static::$types[$cacheName] = $reflectionClass->newInstance();
        }
        return static::$types[$cacheName];
    }

    public static function enumType(string $classMetadata): EnumType
    {
        $reflectionClass = new ReflectionClass($classMetadata);
        if (!$reflectionClass->isSubclassOf(EnumType::class)) {
            $cacheName = $reflectionClass->getShortName();
            if (!isset(static::$types[$cacheName])) {
                static::$types[$cacheName] = $reflectionClass->newInstance();
            }
        } else {
            $cacheName = $reflectionClass->getShortName() . 'EnumGraph';
            if (!isset(static::$types[$cacheName])) {
                static::$types[$cacheName] = new EnumType([
                    'name' => $cacheName,
                    'values' => fn () => DoctrineGraphqlFieldsBuilder::getEnumValues($classMetadata),
                ]);
            }
        }
        return static::$types[$cacheName];
    }

    public static function type(string $classMetadata): Type
    {
        $predefinedInputMetadata = [...self::PREDEFINED_CLASS_MAP, ...static::getPredefinedClassMap()];
        $graphqlClassMetadata = $predefinedInputMetadata[$classMetadata] ?? $classMetadata;

        $reflectionClass = new ReflectionClass($graphqlClassMetadata);
        if ($reflectionClass->isSubclassOf(Type::class)) {
            $cacheName = $reflectionClass->getShortName();
            if (!isset(static::$types[$cacheName])) {
                static::$types[$cacheName] = $reflectionClass->newInstance();
            }
            return static::$types[$cacheName];
        } else {
            return static::inputType($graphqlClassMetadata);
        }
    }
}
