<?php

namespace Resources;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Reflector;

class ReflectionHelper
{

    public static function findAttribute(Reflector $reflection, string $attributeMetadata): ?ReflectionAttribute
    {
        return $reflection->getAttributes($attributeMetadata, \ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;
//        $attributesReflection = $reflection->getAttributes($attributeMetadata, \ReflectionAttribute::IS_INSTANCEOF);
//        return $attributesReflection[0] ?? null;
    }

    public static function hasAttribute(Reflector $reflection, string $attributeMetadata): bool
    {
        return isset($reflection->getAttributes($attributeMetadata, \ReflectionAttribute::IS_INSTANCEOF)[0]) ? true : false;
    }

    public static function getAttributeArgument(Reflector $reflection, string $attributeMetadata, string $argumentName): ?string
    {
        $attribute = static::findAttribute($reflection, $attributeMetadata);
        return $attribute?->getArguments()[$argumentName] ?? null;
    }

    /**
     * 
     * @param Reflector $reflection
     * @param string $attributeMetadata
     * @return ReflectionMethod[]
     */
    public static function getPublicMethods(ReflectionClass $reflection)
    {
        return $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
    }

    /**
     * 
     * @param Reflector $reflection
     * @param string $attributeMetadata
     * @return ReflectionMethod[]
     */
    public static function getPublicMethodsWithAttribute(ReflectionClass $reflection, string $attributeMetadata)
    {
        $response = [];
        foreach (static::getPublicMethods($reflection) as $methodReflection) {
            if (static::hasAttribute($methodReflection, $attributeMetadata)) {
                $response[] = $methodReflection;
            }
        }
        return $response;
    }

    /**
     * 
     * @param \ReflectionMethod $reflection
     * @return ReflectionParameter[]
     */
    public static function getMethodParameters(\ReflectionMethod $reflection)
    {
        return $reflection->getParameters();
    }

    public static function methodHasParameter(\ReflectionMethod $reflection, string $parameterName): bool
    {
        foreach (static::getMethodParameters($reflection) as $parameter) {
            if ($parameter->getName() === $parameterName) {
                return true;
            }
        }
        return false;
    }

    public static function methodHasParameterClass(\ReflectionMethod $reflection, string $parameterClassMetadata): bool
    {
        foreach (static::getMethodParameters($reflection) as $parameter) {
            if ($parameter->getType()->getName() == $parameterClassMetadata) {
                return true;
            }
        }
        return false;
    }
    
    //
    
}
